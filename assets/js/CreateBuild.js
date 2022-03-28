import "../css/Build.scss";
import "../css/CreateBuild.scss";
import "bootstrap/js/dist/tab";
import Popover from "bootstrap/js/dist/popover";
import { $q, $qa, MAX_COL, MAX_ROW, lang } from "./Global";
import { getMethod, getBuildId, setBrightness, initCarCapsPopover, Pop, changePopover } from "./Utils";

const $formBuildName = $q("#formBuildName");
const $formBuildNameInvalid = $q("#formBuildNameInvalid");
const $formBuildType = $q("#formBuildType");
const $formBuildDesc = $q("#formBuildDesc");
const $formBuildDescInvalid = $q("#formBuildDescInvalid");

const $remove5pointsButtons = $qa(".remove5points");
const $remove1pointButtons = $qa(".remove1point");
const $carPointTexts = $qa(".carPointText");
const $add1pointButtons = $qa(".add1point");
const $add5pointsButtons = $qa(".add5points");
const $carBonusRemoveButtons = $qa(".carBonusRemove");
const $carBonus = $qa(".carBonus");
const $carBonusAddButtons = $qa(".carBonusAdd");
const $carProgress = $qa(".carProgress");
const $carBonusProgress = $qa(".carBonusProgress");
const $carCaps = [$qa(".carCap1"), $qa(".carCap2"), $qa(".carCap3"), $qa(".carCap4"), $qa(".carCap5"), $qa(".carCap6")];
const $carTotalPointText = $q("#carTotalPointText");
const $carReset = $q("#carReset");

const $weaponTabs = $qa(".weaponTab");
const $weaponSelects = $qa(".weaponSelect");
const $weaponSidebars = $qa(".weaponSidebar");
const $skillSections = $qa(".skillSection");
const $loadingSpinners = [$qa(".spinner1"), $qa(".spinner2")];

const $progressBars = $qa(".pointProgressBar");
const $pointProgressText = $qa(".pointProgressText");

const $activedSkills = $qa(".activedSkill");
const $activedSkillLists = [$qa(".activedSkillList1"), $qa(".activedSkillList2")];
const $activedSkillSelecteds = [$qa(".activedSkillSelected1"), $qa(".activedSkillSelected2")];
const $activedSkillDeletes = $qa(".activedSkillDelete");

const $weaponResetButtons = $qa(".resetButton");
const $branchNames = [$qa(".branchName1"), $qa(".branchName2")];
const $skillContainers = [$qa(".skill-container1"), $qa(".skill-container2")];
const $svgContainers = [$qa(".svgContainer1"), $qa(".svgContainer2")];

const $visibilityCheck = $q("#visibilityCheck");
const $formBuildSave = $q("#formBuildSave");

window.currentWeapons = [null, null];

/**
 * Associe les skills à "weapon" aprçès un fetch de l'api
 * @param {number} weapon
 */
const getSkills = async (weapon) =>
  (weapon.skills = (await getMethod(`/api/weapons/${weapon.id}/skills`))["hydra:member"]);

/**
 * Met a jour le coutdown progress en fonction des points restants
 * @param {index} weaponIndex
 */
const setCountdown = (weaponIndex) => {
  let n = window.currentWeapons[weaponIndex].countdown[0];
  $progressBars[weaponIndex].style.width = (n * 100) / 19 + "%";
  $pointProgressText[weaponIndex].innerText = window.messageLocal["RemainingPoint"] + n;
};

/**
 * Main : Permet de fetch les json, les weapons
 * et si "/edit", les infos de la build en question
 */
(async () => {
  window.weaponLocal = await getMethod(`/json/${lang}/weapon.json`);
  window.messageLocal = await getMethod(`/json/${lang}/message.json`);
  window.skillLocal = await getMethod(`/json/${lang}/skill.json`);
  initCarCapsPopover($carCaps);
  let data = await getMethod("/api/weapons");
  window.weapons = data["hydra:member"];
  window.weapons.forEach((weapon) => {
    if (weapon.id == 1) return;
    let $weaponOption = document.createElement("option");
    $weaponOption.innerText = window.weaponLocal[weapon.weaponKey];
    $weaponOption.value = weapon.id;
    $weaponSelects.forEach((el) => el.appendChild($weaponOption.cloneNode(true)));
  });
  window.buildId = getBuildId();
  if (window.buildId) {
    let build = await getMethod(`/api/builds/${window.buildId}`);
    if (build.characteristics) {
      window.characteristics = build.characteristics;
      for (let i = 0; i <= 4; i++) setHtmlCar(i);
    }
    build.weapons.forEach(async (weaponIRI, weaponIndex) => {
      $loadingSpinners[weaponIndex].forEach((el) => el.classList.remove("d-none"));
      let weapon = window.weapons.filter((w) => w["@id"] == weaponIRI)[0];
      if (weapon) {
        $weaponSelects[weaponIndex].value = weapon?.id && null;
        await getSkills(weapon);
        weapon.countdown = [19, 0, 0];
        build.selectedSkills[weaponIndex].forEach((skillIRI) => {
          let skill = weapon.skills.filter((s) => s["@id"] == skillIRI)[0];
          skill.selected = true;
          weapon.countdown[0]--;
          weapon.countdown[skill.side]++;
        });
        weapon.activedSkills = build.activedSkills[weaponIndex];
        $weaponSelects[weaponIndex].value = weapon.id;
        await changeWeapon(weaponIndex, weapon.id);
      }
    });
  }
  formBuildSave.classList.remove("d-none");
})();

// const $carPointTexts = $qa(".carPointText");
const setHtmlCar = (car) => {
  let point = window.characteristics[1][car];
  let bonusPoint = window.characteristics[2][car];
  $carTotalPointText.innerText = window.characteristics[0];
  $carPointTexts[car].innerText = point + 5;
  $carBonus[car].value = bonusPoint;
  $carProgress[car].style.width = `${((point + 5) * 100) / 300}%`;
  $carBonusProgress[car].style.width = `${(bonusPoint * 100) / 300}%`;
  for (let i = 0; i <= 5; i++) {
    if (point + 5 - 50 >= i * 50) $carCaps[car][i].style.backgroundColor = "#FFC107";
    else if (point + 5 + bonusPoint - 50 >= i * 50) $carCaps[car][i].style.backgroundColor = "#0D6EFD";
    else $carCaps[car][i].style.backgroundColor = "#fff";
  }
};

const setCar = (car, add, n) => {
  if (add) {
    if (window.characteristics[0] - n < 0) return;
    window.characteristics[0] -= n;
    window.characteristics[1][car] += n;
  } else {
    if (window.characteristics[0] + n > 190) return;
    if (window.characteristics[1][car] - n < 0) return;
    window.characteristics[0] += n;
    window.characteristics[1][car] -= n;
  }
  setHtmlCar(car);
};

const resetCar = () => {
  window.characteristics = [190, [0, 0, 0, 0, 0, 0], [0, 0, 0, 0, 0, 0]];
  for (let i = 0; i <= 4; i++) {
    $carBonus[i].value = 0;
    setHtmlCar(i);
  }
};
resetCar();

// const $remove5pointsButtons = $qa(".remove5points");
$remove5pointsButtons.forEach(($remove5pointsButton, car) => {
  $remove5pointsButton.addEventListener("click", () => setCar(car, false, 5));
});

// const $remove1pointButtons = $qa(".remove1point");
$remove1pointButtons.forEach(($remove1pointButton, car) => {
  $remove1pointButton.addEventListener("click", () => setCar(car, false, 1));
});

// const $add1pointButtons = $qa(".add1point");
$add1pointButtons.forEach(($add1pointButton, car) => {
  $add1pointButton.addEventListener("click", () => setCar(car, true, 1));
});

// const $add5pointsButtons = $qa(".add5points");
$add5pointsButtons.forEach(($add5pointsButton, car) => {
  $add5pointsButton.addEventListener("click", () => setCar(car, true, 5));
});

const setBonus = (car, buff) => {
  if (buff) buff == "1" ? $carBonus[car].value++ : $carBonus[car].value--;
  if ($carBonus[car].value == "") $carBonus[car].value = 0;
  if ($carBonus[car].value < 0 || $carBonus[car].value >= 1000) $carBonus[car].value = $carBonus[car].dataset.old;
  window.characteristics[2][car] = parseInt($carBonus[car].value);
  $carBonus[car].dataset.old = $carBonus[car].value;
  setHtmlCar(car);
};

$carBonus.forEach(($carB, car) => {
  $carB.addEventListener("change", () => {
    setBonus(car);
  });
});

// const $carBonusRemoveButtons = $qa(".carBonusRemove");
$carBonusRemoveButtons.forEach(($carBonusRemoveButton, car) => {
  $carBonusRemoveButton.addEventListener("click", () => setBonus(car, -1));
});

// const $carBonusAddButtons = $qa(".carBonusAdd");
$carBonusAddButtons.forEach(($carBonusAddButton, car) => {
  $carBonusAddButton.addEventListener("click", () => setBonus(car, 1));
});

$carReset.addEventListener("click", () => {
  resetCar();
});

/**
 * Mise en place d'une arme (weaponId) dans l'onglet correspondant (weaponIndex)
 * Fetch les skills si ce n'est pas déja fait. Mise en forme des skills, activedSkills
 * coutdown en fonction de la séléction de l'utilisation
 * @param {number} weaponIndex
 * @param {number} weaponId
 */
const changeWeapon = async (weaponIndex, weaponId) => {
  $weaponSidebars[weaponIndex].classList.add("d-none");
  $skillSections[weaponIndex].classList.add("d-none");
  $loadingSpinners[weaponIndex].forEach((el) => el.classList.remove("d-none"));
  if (weaponId == 0) {
    window.currentWeapons[weaponIndex] = null;
    $loadingSpinners[weaponIndex].forEach((el) => el.classList.add("d-none"));
    return;
  }
  $weaponTabs.forEach((el) => el.classList.remove("text-danger"));
  window.currentWeapons[weaponIndex] = window.weapons.filter((w) => w.id == weaponId)[0];
  let weapon = window.currentWeapons[weaponIndex];
  let $diffWeaponSelectOptions = Array.from($weaponSelects[weaponIndex == 0 ? 1 : 0].options);
  $diffWeaponSelectOptions.forEach((el) => el.classList.remove("d-none"));
  $diffWeaponSelectOptions.filter((el) => el.value == weaponId)[0].classList.add("d-none");
  $branchNames[weaponIndex].forEach((el, i) => (el.innerText = window.weaponLocal[weapon.branch[i]]));
  if (!weapon.skills) await getSkills(weapon, weapon.id);
  if (!weapon.activedSkills) weapon.activedSkills = [null, null, null];
  if (!weapon.countdown) weapon.countdown = [19, 0, 0];
  if (!weapon.skillParsed) {
    let skillInfoLocal = await getMethod(`/json/${weapon.weaponKey}.json`);
    let skillLocal = JSON.stringify(window.skillLocal);
    Object.keys(skillInfoLocal).forEach((k) => {
      skillLocal = skillLocal.replaceAll(k, skillInfoLocal[k]);
    });
    window.skillLocal = JSON.parse(skillLocal);
    weapon.skillParsed = true;
  }
  setCountdown(weaponIndex);
  $skillContainers[weaponIndex].forEach((el) => Pop(el).disable());
  $svgContainers[weaponIndex].forEach((el) => (el.firstElementChild.innerHTML = ""));
  for (let c = 1; c <= 3; c++) {
    let $activedSkills = $activedSkillLists[weaponIndex][c - 1].children;
    $activedSkills.forEach(($activedSkill) => {
      $activedSkill.firstElementChild.dataset.id = 0;
      $activedSkill.firstElementChild.src = "/img/emptyCadre.png";
      $activedSkill.classList.add("d-none");
    });
    let $activedSkillSelected = $activedSkillSelecteds[weaponIndex][c - 1];
    $activedSkillSelected.dataset.id = 0;
    $activedSkillSelected.src = "/img/emptyCadre.png";
  }
  let activedSkillCount = 1;
  $skillContainers[weaponIndex].forEach(($skillContainer) => {
    $skillContainer.style.filter = `brightness(1)`;
    let data = $skillContainer.id.split("-");
    let match = weapon.skills.filter((s) => s.side == data[2] && s.line == data[3] && s.col == data[4])[0];
    $skillContainer.style.backgroundImage = match
      ? `url('/img/bg/bg${match.bgColor}${match.type == 1 ? "" : "c"}.png')`
      : "";
    $skillContainer.style.backgroundSize = match ? ([1, 3].includes(match.type) ? "90% 90%" : "70% 70%") : "";
    $skillContainer.firstElementChild.style.backgroundImage = match
      ? `url(/img/skill/${weapon.weaponKey}/${match.skillKey}.png)`
      : "";
    $skillContainer.firstElementChild.style.backgroundSize = match
      ? [1, 3].includes(match.type)
        ? "90% 90%"
        : "70% 70%"
      : "";
    $skillContainer.dataset.id = match ? match.id : 0;
    changePopover({ el: $skillContainer, skill: match }, true);
    if (match) {
      Pop($skillContainer).enable();
      if (match.selected == undefined) match.selected = false;
      setBrightness($skillContainer, match);
      if (match.parent) {
        let parentMatch = weapon.skills.filter((s) => s["@id"] == match.parent)[0];
        if (parentMatch) {
          let bgSVG = $svgContainers[weaponIndex][match.side - 1].firstElementChild;
          bgSVG.innerHTML += `<line class="skillLine" 
            x1="${(parentMatch.col * 100) / MAX_COL - 10}%" y1="${(parentMatch.line * 100) / MAX_ROW - 10}%" 
            x2="${(match.col * 100) / MAX_COL - 10}%" y2="${(match.line * 100) / MAX_ROW - 10}%"/>`;
        }
      }
      if (match.type == 1) {
        let isSelected = false;
        let $activedSkills = [];
        $activedSkillLists[weaponIndex].forEach((ul) =>
          $activedSkills.push(ul.querySelector(`img[data-li="${activedSkillCount}"]`))
        );
        $activedSkills.forEach(($activedSkill, index) => {
          $activedSkill.src = `/img/skill/${weapon.weaponKey}/${match.skillKey}.png`;
          $activedSkill.dataset.id = match.id;
          if (match.selected) {
            $activedSkill.parentElement.classList.remove("d-none");
            if (weapon.activedSkills[index] == match["@id"]) {
              isSelected = true;
              let $activedSkillSelected = $activedSkillSelecteds[weaponIndex][index];
              $activedSkillSelected.src = $activedSkill.src;
              $activedSkillSelected.dataset.id = $activedSkill.dataset.id;
            }
          } else $activedSkill.parentElement.classList.add("d-none");
        });
        if (isSelected) $activedSkills.forEach((el) => el.parentElement.classList.add("d-none"));
        activedSkillCount++;
      }
    }
  });
  $loadingSpinners[weaponIndex].forEach((el) => el.classList.add("d-none"));
  $weaponSidebars[weaponIndex].classList.remove("d-none");
  $skillSections[weaponIndex].classList.remove("d-none");
};

/**
 * Event du changement d'arme
 */
$weaponSelects.forEach(($weaponSelect, weaponIndex) => {
  $weaponSelect.addEventListener("change", () => {
    const $selectedOption = $weaponSelect.options[$weaponSelect.selectedIndex];
    if (window.currentWeapons[weaponIndex]?.id == $selectedOption.value) return;
    if (window.currentWeapons[weaponIndex] == null && $selectedOption.value == 0) return;
    changeWeapon(weaponIndex, $selectedOption.value);
  });
});

/**
 * Event du click sur un skill
 * Condition activation / desactivation
 * Mise a jour activedSkill si besoin
 */
$skillContainers.forEach(($skillContainers, weaponIndex) => {
  $skillContainers.forEach(($skillContainer) => {
    new Popover($skillContainer, { title: "Titre", content: "Description", trigger: "hover", html: true });
    $skillContainer.addEventListener("click", () => {
      let weapon = window.currentWeapons[weaponIndex];
      let skillId = $skillContainer.dataset.id;
      if (skillId == 0) return;
      let skill = window.currentWeapons[weaponIndex].skills.filter((s) => s.id == skillId)[0];
      if (!skill) return;

      //Conditions pour allumer le skill
      if (!skill.selected) {
        // 1. Si tous les point utiliser
        if (weapon.countdown[0] == 0) {
          changePopover({ el: $skillContainer, skill, key: "NoMorePoint" });
          return;
        }

        // 2. Si skill enfant, si skill parent unselected
        if (skill.parent) {
          let parent = weapon.skills.filter((s) => s["@id"] == skill.parent)[0];
          if (!parent.selected) {
            changePopover({
              el: $skillContainer,
              skill,
              key: "TopSkill",
              suffix: window.skillLocal[parent.skillKey] ?? parent.skillKey,
            });
            return;
          }
        }
        // 3. Si pas la première ligne, si aucun skill ligne précédente selected
        if (
          skill.line != 1 &&
          weapon.skills.filter((s) => s.side == skill.side && s.line == skill.line - 1 && s.selected).length == 0
        ) {
          changePopover({ el: $skillContainer, skill, key: "Rowtop" });
          return;
        }

        // 4. Si ultimate, si pas 10 point attribué sur le side
        if (skill.line == 6) {
          if (weapon.countdown[skill.side] < 10) {
            changePopover({
              el: $skillContainer,
              skill,
              key: "TenPointSelect",
              suffix: window.weaponLocal[weapon.branch[skill.side - 1]] ?? weapon.branch[skill.side - 1],
            });
            return;
          }
        }

        skill.selected = true;
        setBrightness($skillContainer, skill);
        changePopover({ el: $skillContainer, skill });
        weapon.countdown[0]--;
        weapon.countdown[skill.side]++;
        if (skill.type == 1) {
          $qa(`.activedSkill[data-id="${skill.id}"]`).forEach(($activedSkill) => {
            $activedSkill.parentElement.classList.remove("d-none");
          });
        }
      }

      //Conditions pour eteindre le skill
      else {
        // 1. Si skill parent, si skill enfant selected
        if (skill.children.length > 0) {
          let ActiveChildren = skill.children.filter(
            (c) => weapon.skills.filter((s) => s["@id"] == c && s.selected)[0]
          );
          if (ActiveChildren.length > 0) {
            changePopover({
              el: $skillContainer,
              skill,
              key: "BottomSkill",
              suffix: window.skillLocal[ActiveChildren[0].skillKey] ?? ActiveChildren[0].skillKey,
            });
            return;
          }
        }

        // 2. Si skills ligne suivante selected et pas de skill meme ligne selected
        if (
          skill.line != 6 &&
          weapon.skills.filter((s) => s.side == skill.side && s.line == skill.line + 1 && s.selected).length > 0
        ) {
          if (weapon.skills.filter((s) => s.side == skill.side && s.line == skill.line && s.selected).length <= 1) {
            changePopover({ el: $skillContainer, skill, key: "RowBottom" });
            return;
          }
        }

        // 3. Si point dépenser dans la branche = 11, si skill derniere ligne selected
        if (skill.line != 6 && weapon.countdown[skill.side] == 11) {
          let LastLineSkill = weapon.skills.filter((s) => s.side == skill.side && s.line == 6 && s.selected);
          if (LastLineSkill.length > 0) {
            changePopover({
              el: $skillContainer,
              skill,
              key: "BottomSkill",
              suffix: window.skillLocal[LastLineSkill[0].skillKey] ?? LastLineSkill[0].skillKey,
            });
            return;
          }
        }

        skill.selected = false;
        setBrightness($skillContainer, skill);
        changePopover({ el: $skillContainer, skill });
        weapon.countdown[0]++;
        weapon.countdown[skill.side]--;
        if (skill.type == 1) {
          $qa(`.activedSkill[data-id="${skill.id}"]`).forEach(($activedSkill, index) => {
            $activedSkill.parentElement.classList.add("d-none");
            let $activedSkillSelected = $activedSkillSelecteds[weaponIndex][index];
            if ($activedSkillSelected.dataset.id == skill.id) {
              $activedSkillSelected.dataset.id = 0;
              $activedSkillSelected.src = "/img/emptyCadre.png";
              weapon.activedSkills[index] = null;
            }
          });
        }
      }
      setCountdown(weaponIndex);
    });
  });
});

/**
 * Event click sur un activedSkill
 */
$activedSkills.forEach(($activedSkill) => {
  $activedSkill.addEventListener("click", () => {
    let data = $activedSkill.id.split("-");
    let weaponIndex = data[1] - 1;
    let cadre = data[2];
    let skillId = $activedSkill.dataset.id;
    let $activedSkillSelected = $activedSkillSelecteds[weaponIndex][cadre - 1];
    $qa(`.activedSkill[data-id="${$activedSkillSelected.dataset.id}"]`).forEach((el) =>
      el.parentElement.classList.remove("d-none")
    );
    $qa(`.activedSkill[data-id="${skillId}"]`).forEach((el) => el.parentElement.classList.add("d-none"));
    $activedSkillSelected.dataset.id = skillId;
    $activedSkillSelected.src = $activedSkill.src;
    window.currentWeapons[weaponIndex].activedSkills[cadre - 1] = "/api/skills/" + skillId;
  });
});

/**
 * Event de suppresion d'un activedSkill
 */
$activedSkillDeletes.forEach(($activedSkillDelete) => {
  $activedSkillDelete.addEventListener("click", (e) => {
    let data = $activedSkillDelete.id.split("-");
    let weaponIndex = data[1] - 1;
    let cadre = data[2];
    let $activedSkillSelected = $activedSkillSelecteds[weaponIndex][cadre - 1];
    let skillId = $activedSkillSelected.dataset.id;
    $qa(`.activedSkill[data-id="${skillId}"]`).forEach((el) => el.parentElement.classList.remove("d-none"));
    $activedSkillSelected.dataset.id = 0;
    $activedSkillSelected.src = "/img/emptyCadre.png";
    window.currentWeapons[weaponIndex].activedSkills[cadre - 1] = null;
  });
});

/**
 * Event de reset
 */
$weaponResetButtons.forEach(($weaponResetButton, weaponIndex) => {
  $weaponResetButton.addEventListener("click", () => {
    let weapon = window.currentWeapons[weaponIndex];
    weapon.skills.forEach((s) => (s.selected = false));
    weapon.countdown = [19, 0, 0];
    weapon.activedSkills = [null, null, null];
    changeWeapon(weaponIndex, weapon.id);
  });
});

$formBuildName.addEventListener("change", () => {
  if ($formBuildName.value.length >= 8 && $formBuildName.value.length <= 80)
    $formBuildNameInvalid.classList.add("d-none");
});

$formBuildDesc.addEventListener("change", () => {
  if ($formBuildDesc.value.length <= 3000) $formBuildDescInvalid.classList.add("d-none");
});

$formBuildSave.addEventListener("click", async () => {
  $formBuildSave.disabled = true;
  formBuildSaveLoader.classList.remove("d-none");
  let build = {
    name: $formBuildName.value,
    description: $formBuildDesc.value,
    type: parseInt($formBuildType.value),
    weapons: window.currentWeapons.map((w) => w?.["@id"] || null),
    selectedSkills: window.currentWeapons.map((w) => w?.skills.filter((s) => s.selected).map((s) => s["@id"]) || []),
    activedSkills: window.currentWeapons.map((w) => w?.activedSkills || [null, null, null]),
    characteristics: window.characteristics,
    private: $visibilityCheck.checked,
  };

  let error = false;

  if (build.name.length < 8 || build.name.length > 60) {
    $formBuildNameInvalid.classList.remove("d-none");
    error = true;
  }

  if (build.description.length > 3000) {
    $formBuildDescInvalid.classList.remove("d-none");
    error = true;
  }

  if (error) {
    window.scrollTo({
      top: 0,
      behavior: "smooth",
    });
  }

  if (!build.weapons[0] && !build.weapons[1]) {
    $weaponTabs.forEach((el) => el.classList.add("text-danger"));
    error = true;
  }

  if (!error) {
    let response = await fetch(`/api/builds${window.buildId ? `/${window.buildId}` : ""}`, {
      headers: { "Content-Type": "application/json" },
      method: window.buildId ? "PUT" : "POST",
      body: JSON.stringify(build),
    });
    let data = await response.json();
    if (200 <= response.status && response.status < 300) {
      window.location.href = "/build/" + data.id;
    } else alert("Server Error, Please contact Admin\n" + data["hydra:description"]);
  }

  $formBuildSave.disabled = false;
  formBuildSaveLoader.classList.add("d-none");
});
