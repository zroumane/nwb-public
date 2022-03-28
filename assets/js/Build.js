import "../css/Build.scss";
import "bootstrap/js/dist/tab";
import Popover from "bootstrap/js/dist/popover";
import { $q, $qa, MAX_COL, MAX_ROW, lang } from "./Global";
import { getMethod, getBuildId, setBrightness, initCarCapsPopover, changePopover } from "./Utils";

const $spinner = $q("#spinner");
const $shareButton = $q("#shareButton");
const $carTexts = $qa(".carPointText");
const $carBonusTexts = $qa(".carPointBonusText");
const $carProgress = $qa(".carProgress");
const $carBonusProgress = $qa(".carBonusProgress");
const $carCaps = [$qa(".carCap1"), $qa(".carCap2"), $qa(".carCap3"), $qa(".carCap4"), $qa(".carCap5"), $qa(".carCap6")];
const $buildTabs = $qa(".buildTab");
const $skillSection = $q("#skillSection");
const $branchNames = [$qa(".branchName1"), $qa(".branchName2")];
const $svgContainers = [$qa(".svgContainer1"), $qa(".svgContainer2")];

(async () => {
  window.messageLocal = await getMethod(`/json/${lang}/message.json`);
  window.weaponLocal = await getMethod(`/json/${lang}/weapon.json`);
  window.skillLocal = await getMethod(`/json/${lang}/skill.json`);
  initCarCapsPopover($carCaps);
  let data = await fetch(`/api/builds/${getBuildId()}`, {
    cache: "no-store",
  });
  let build = await data.json();
  if (!build.characteristics) {
    build.characteristics = [190, [0, 0, 0, 0, 0, 0], [0, 0, 0, 0, 0, 0]];
  }
  for (let car = 0; car <= 4; car++) {
    let point = build.characteristics[1][car];
    let bonusPoint = build.characteristics[2][car];
    $carTexts[car].innerText = point + 5;
    $carBonusTexts[car].innerText = "+" + bonusPoint;
    $carProgress[car].style.width = `${((point + 5) * 100) / 300}%`;
    $carBonusProgress[car].style.width = `${(bonusPoint * 100) / 300}%`;
    for (let i = 0; i <= 5; i++) {
      if (point + 5 - 50 >= i * 50) $carCaps[car][i].style.backgroundColor = "#FFC107";
      else if (point + 5 + bonusPoint - 50 >= i * 50) $carCaps[car][i].style.backgroundColor = "#0D6EFD";
      else $carCaps[car][i].style.backgroundColor = "#fff";
    }
  }

  build.weapons.forEach(async (weaponIRI, weaponIndex) => {
    if (weaponIRI) {
      let weapon = await (await fetch(weaponIRI)).json();
      let skillInfoLocal = await getMethod(`/json/${weapon.weaponKey}.json`);
      let skillLocal = JSON.stringify(window.skillLocal);
      Object.keys(skillInfoLocal).forEach((k) => {
        skillLocal = skillLocal.replaceAll(k, skillInfoLocal[k]);
      });
      window.skillLocal = JSON.parse(skillLocal);
      weapon.branch.forEach((b, i) => {
        $branchNames[weaponIndex][i].innerText = window.weaponLocal[b];
      });
      weapon.skills = (await (await fetch(weaponIRI + "/skills")).json())["hydra:member"];
      weapon.skills.forEach(async (skill) => {
        let $skillContainer = $q(`#skill-${weaponIndex + 1}-${skill.side}-${skill.line}-${skill.col}`);
        $skillContainer.style.backgroundImage = `url('/img/bg/bg${skill.bgColor}${skill.type == 1 ? "" : "c"}.png')`;
        $skillContainer.style.backgroundSize = [1, 3].includes(skill.type) ? "90% 90%" : "70% 70%";
        $skillContainer.firstElementChild.style.backgroundImage = `url(/img/skill/${weapon.weaponKey}/${skill.skillKey}.png)`;
        $skillContainer.firstElementChild.style.backgroundSize = [1, 3].includes(skill.type) ? "90% 90%" : "70% 70%";
        build.selectedSkills[weaponIndex].includes(skill["@id"]) ? (skill.selected = true) : (skill.selected = false);
        setBrightness($skillContainer, skill);
        new Popover($skillContainer, { trigger: "hover", html: true });
        changePopover({ el: $skillContainer, skill }, true);
        if (skill.parent) {
          let parent = weapon.skills.filter((s) => s["@id"] == skill.parent)[0];
          if (parent) {
            let bgSVG = $svgContainers[weaponIndex][skill.side - 1].firstElementChild;
            bgSVG.innerHTML += `<line class="skillLine" 
              x1="${(parent.col * 100) / MAX_COL - 10}%" y1="${(parent.line * 100) / MAX_ROW - 10}%" 
              x2="${(skill.col * 100) / MAX_COL - 10}%" y2="${(skill.line * 100) / MAX_ROW - 10}%"/>`;
          }
        }
      });
      build.activedSkills[weaponIndex].forEach((activedSkill, i) => {
        if (activedSkill) {
          let match = weapon.skills.filter((s) => s["@id"] == activedSkill)[0];
          let $activedSkill = $q(`#activedSkill-${weaponIndex + 1}-${i + 1}`);
          $activedSkill.src = `/img/skill/${weapon.weaponKey}/${match.skillKey}.png`;
          new Popover($activedSkill, { trigger: "hover", html: true });
          changePopover({ el: $activedSkill, skill: match }, true);
        }
      });
      $buildTabs[weaponIndex].innerText = window.weaponLocal[weapon.weaponKey];
      $buildTabs[weaponIndex].classList.remove("disabled");
    }
  });
  $spinner.classList.add("d-none");
  $skillSection.classList.remove("d-none");
})();

$shareButton.addEventListener("click", () => {
  let url = window.location.origin + window.location.pathname.substr(3);
  navigator.clipboard.writeText(url);
  $shareButton.firstElementChild.innerText = "✓";
  $shareButton.classList.remove("btn-primary");
  $shareButton.classList.add("btn-success");
});
