import "../css/WeaponDashboard.scss";
import { $q, $qa, MAX_ROW, MAX_COL } from "./Global";
import { getMethod } from "./Utils";

const $weaponSelect = $q("#weaponSelect");
const $weaponForm = $q("#weaponForm");
const $weaponFormKey = $weaponForm.querySelector('input[data-type="wkey"]');
const $weaponFormB1 = $weaponForm.querySelector('input[data-type="b1key"]');
const $weaponFormB2 = $weaponForm.querySelector('input[data-type="b2key"]');
const $sendWeaponBtn = $q('.weaponAction[data-type="submit"]');
const $deleteWeaponBtn = $q('.weaponAction[data-type="delete"]');

const $skillSection = $q("#skillSection");
const $svgContainer = $qa(".svgContainer");

const $skillForm = $q("#skillForm");
const $skillFormTitle = $skillForm.querySelector("#skillFormTitle");
const $skillFormTitleId = $skillForm.querySelector("#skillFormTitleId");
const $skillFormId = $skillForm.querySelector("#skillFormId");
const $skillFormSkillKey = $skillForm.querySelector("#skillFormSkillKey");
const $skillFormCooldown = $skillForm.querySelector("#skillFormCooldown");
const $skillFormType = $skillForm.querySelector("#skillFormType");
const $skillFormBgColor = $skillForm.querySelector("#skillFormBgColor");
const $skillFormParent = $skillForm.querySelector("#skillFormParent");
const $skillFormParentDelete = $skillForm.querySelector("#skillFormParentDelete");
const $skillFormSide = $skillForm.querySelector("#skillFormSide");
const $skillFormRow = $skillForm.querySelector("#skillFormRow");
const $skillFormCol = $skillForm.querySelector("#skillFormCol");
const $skillFormSend = $q('.skillAction[data-type="submit"]');
const $skillFormDelete = $q('.skillAction[data-type="delete"]');

/**
 * Retire l'outline de l'ancien skill selectionné
 */
const clearSkillOutline = () => {
  let lastSkill = $q(`#skill-${$skillFormSide.value}-${$skillFormRow.value}-${$skillFormCol.value}`);
  if (lastSkill) lastSkill.style.outline = "";
};

/**
 * Requete adaptative
 * @param {string} url
 * @param {string} method
 * @param {object} body
 * @param {void} callback
 */
const request = async (url, method, body, callback) => {
  let args = { headers: { "Content-Type": "application/json" }, method: method };
  if (body != undefined) args.body = JSON.stringify(body);
  let response = await fetch(url, args);
  if (200 <= response.status && response.status < 300) window.setTimeout(() => callback(), 500);
  else response.json().then((d) => alert(d["hydra:description"]));
};

/**
 * Ajout / Update skill
 * @param {string} type
 * @param {number} id
 * @param {object} body
 * @param {void} callback
 */
const postEntity = (type, id, body, callback) => {
  let method;
  if (id == 0) method = "POST";
  else method = "PUT";
  request(`/api/${type}${id == 0 ? "" : "/" + id}`, method, body, callback);
};

/**
 * Mise a jour de la liste d'arme
 */
const getWeapon = async () => {
  let data = await getMethod("/api/weapons");
  window.weapons = data["hydra:member"];
  Array.from($weaponSelect.children)
    .filter((c) => c.value != 0)
    .forEach((c) => c.remove());
  window.weapons.forEach((weapon) => {
    let $weaponOption = document.createElement("option");
    $weaponOption.value = weapon.id;
    $weaponOption.innerText = weapon.weaponKey;
    $weaponSelect.appendChild($weaponOption);
  });
  Array.from($weaponSelect.children).filter((c) => c.value == 0)[0].selected = true;
  updateWeaponForm(undefined);
};
getWeapon();

/**
 * Mise a jour du contenu en fonction de l'arme selectionnée
 */
const updateWeaponForm = (weapon) => {
  $skillForm.classList.add("isHidden");
  $weaponFormKey.value = weapon ? weapon.weaponKey : "";
  $weaponFormB1.value = weapon ? weapon.branch[0] : "";
  $weaponFormB2.value = weapon ? weapon.branch[1] : "";
  $q("#branchName-1").innerText = weapon ? weapon.branch[0] : "";
  $q("#branchName-2").innerText = weapon ? weapon.branch[1] : "";
  if (weapon != undefined) {
    window.currentWeapon = weapon.id;
    window.currentWeaponKey = weapon.weaponKey;
    getSkills();
  }
};

/**
 * Event bouton Ajouter / Mettre a jour une arme
 */
$sendWeaponBtn.addEventListener("click", () => {
  $skillSection.classList.add("isHidden");
  postEntity("weapons", $weaponSelect.value, { weaponKey: $weaponFormKey.value, branch: [$weaponFormB1.value, $weaponFormB2.value] }, getWeapon);
});

/**
 *  Event boutton Supprimer une arme
 */
$deleteWeaponBtn.addEventListener("click", () => {
  $skillSection.classList.add("isHidden");
  if ($weaponSelect.value == 0) return;
  if (window.confirm(`Delete weapon "${$weaponSelect.options[$weaponSelect.selectedIndex].innerText}" ?`)) {
    request(`/api/weapons/${$weaponSelect.value}`, "DELETE", undefined, getWeapon);
  }
});

/**
 * Event changement d'arme
 */
$weaponSelect.addEventListener("change", () => {
  $skillSection.classList.add("isHidden");
  let selectedValue = $weaponSelect.value;
  if (selectedValue == 0) return updateWeaponForm(undefined);
  updateWeaponForm(window.weapons.filter((w) => w.id == selectedValue)[0]);
});

/**
 * Mise a jour des skills
 */
const getSkills = async () => {
  $skillForm.classList.add("isHidden");
  let data = await getMethod(`/api/weapons/${window.currentWeapon}/skills`);
  window.currentSkills = data["hydra:member"];
  $svgContainer.forEach((el) => (el.firstElementChild.innerHTML = ""));
  $qa(".skill-container").forEach((c) => {
    let d = c.id.split("-");
    let match = window.currentSkills.filter((s) => s.side == d[1] && s.line == d[2] && s.col == d[3])[0];
    if (match) {
      c.style.backgroundImage = `url('/img/bg/bg${match.bgColor}${match.type == 1 ? "" : "c"}.png')`;
      c.style.backgroundSize = [1, 3].includes(match.type) ? "90% 90%" : "70% 70%";
      c.firstElementChild.style.backgroundImage = `url(/img/skill/${window.currentWeaponKey}/${match.skillKey}.png)`;
      c.firstElementChild.style.backgroundSize = [1, 3].includes(match.type) ? "90% 90%" : "70% 70%";

      c.dataset.id = match.id;
      if (match.parent != undefined) {
        let parentMatch = window.currentSkills.filter((s) => s["@id"] == match.parent)[0];
        if (parentMatch) {
          let bgSVG = $svgContainer[match.side - 1].firstElementChild;
          bgSVG.innerHTML += `<line class="skillLine" 
            x1="${(parentMatch.col * 100) / MAX_COL - 10}%" y1="${(parentMatch.line * 100) / MAX_ROW - 10}%" 
            x2="${(match.col * 100) / MAX_COL - 10}%" y2="${(match.line * 100) / MAX_ROW - 10}%"/>`;
        }
      }
    } else {
      c.style.backgroundImage = "";
      c.firstElementChild.style.backgroundImage = "";
      c.dataset.id = 0;
    }
  });
  clearSkillOutline();
  $skillSection.classList.remove("isHidden");
};

/**
 * Event Ajouter / Mettre a jour un skill
 */
$skillFormSend.addEventListener("click", () => {
  var type = parseInt($skillFormType.querySelector('[type="radio"]:checked').value);
  let body = {
    skillKey: $skillFormSkillKey.value,
    weapon: `/api/weapons/${window.currentWeapon}`,
    side: parseInt($skillFormSide.value),
    line: parseInt($skillFormRow.value),
    col: parseInt($skillFormCol.value),
    cooldown: parseFloat($skillFormCooldown.value),
    bgColor: parseInt($skillFormBgColor.querySelector('[type="radio"]:checked').value),
    type: type,
    parent: $skillFormParent.dataset.parentId != 0 ? `/api/skills/${$skillFormParent.dataset.parentId}` : null,
  };
  if ($skillFormParent.dataset.parentId != 0) body.parent = `/api/skills/${$skillFormParent.dataset.parentId}`;
  postEntity("skills", $skillFormId.value, body, getSkills);
});

/**
 * Event supprimer un skill
 */
$skillFormDelete.addEventListener("click", () => {
  let skillId = $skillFormId.value;
  if (skillId == 0) return;
  if (window.confirm(`Delete skill "${window.currentSkills.filter((s) => s.id == skillId)[0].skillKey}" ?`)) {
    request(`/api/skills/${skillId}`, "DELETE", undefined, getSkills);
  }
});

/**
 * Event drap and drop skill parent
 */
$skillFormParent.addEventListener("dragover", (event) => event.preventDefault());
$skillFormParent.addEventListener("drop", (event) => {
  event.preventDefault();
  if (window.$draggedSkill.dataset.id == 0 || window.$draggedSkill.dataset.id == $skillFormId.value) return;
  $skillFormParent.dataset.parentId = window.$draggedSkill.dataset.id;
  $skillFormParent.style.backgroundImage = window.$draggedSkill.firstElementChild.style.backgroundImage;
});
$skillFormParentDelete.addEventListener("click", () => {
  $skillFormParent.dataset.parentId = 0;
  $skillFormParent.style.backgroundImage = "";
});

$qa(".skill-container").forEach((skillContainer) => {
  /**
   * Event click sur un skill container
   */
  skillContainer.addEventListener("click", async () => {
    await clearSkillOutline();
    skillContainer.style.outline = "3px blue solid";
    let data = skillContainer.id.split("-");
    let skillId = skillContainer.dataset.id;
    if (skillId == 0) {
      $skillFormTitle.innerText = "Create Skill";
      $skillFormTitleId.innerText = "";
    } else {
      $skillFormTitle.innerText = "Update Skill ";
      $skillFormTitleId.innerText = skillId;
    }
    let match = window.currentSkills.filter((s) => s.id == skillId)[0];
    $skillFormSkillKey.value = match ? match.skillKey : "";
    $skillFormCooldown.value = match ? match.cooldown : "";
    $skillFormType.querySelector(`input[value="${match ? match.type : 1}"]`).checked = true;
    $skillFormBgColor.querySelector(`input[value="${match ? match.bgColor : 0}"]`).checked = true;
    $skillFormParent.dataset.parentId = match && match.parent ? match.parent.split("/").reverse()[0] : "";
    $skillFormParent.style.backgroundImage =
      match && match.parent ? `url("/img/skill/${window.currentWeaponKey}/${window.currentSkills.filter((s) => s["@id"] == match.parent)[0].skillKey}.png")` : "";
    $skillFormId.value = skillId;
    $skillFormSide.value = data[1];
    $skillFormRow.value = data[2];
    $skillFormCol.value = data[3];
    $skillForm.classList.remove("isHidden");
  });

  /**
   * Event drag et drop skill container
   */
  skillContainer.addEventListener("dragover", (event) => event.preventDefault());
  skillContainer.addEventListener("dragenter", (event) => (event.target.style.outline = "3px red solid"));
  skillContainer.addEventListener("dragleave", (event) => (event.target.style.outline = ""));
  skillContainer.addEventListener("drop", (event) => {
    event.preventDefault();
    event.target.style.outline = "";
    var target = event.target.parentElement;
    if (!target.classList.contains("skill-container-icon")) return;
    if (target.parentElement == window.$draggedSkill) return;
    let match = window.currentSkills.filter((s) => s.id == window.$draggedSkill.dataset.id)[0];
    if (match == undefined) return;
    let data = target.parentElement.id.split("-");
    if (target.parentElement.dataset.id != "0") {
      window.alert("There is already a skill at this place");
      return;
    }
    if (
      window.confirm(`Move "${match.skillKey}" from side:${match.side}, row:${match.line}, 
        col:${match.col} to side:${data[1]}, row:${data[2]}, col:${data[3]} ?`)
    ) {
      postEntity("skills", match.id, { side: parseInt(data[1]), line: parseInt(data[2]), col: parseInt(data[3]) }, getSkills);
    }
  });
});
