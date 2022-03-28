import "../css/Builds.scss";
import { $q } from "./Global";

const $filterBuildForm = $q("#filterBuildForm");
const $filterBuildReset = $q("#filterBuildReset");
const $filterBuildSearch = $q("#filterBuildSearch");
const $filterBuildWeapon = $q("#filterBuildWeapon");
const $allWeaponCheck = $q("#allWeaponCheck").querySelector("input");
const $weaponsChecks = Array.from($filterBuildWeapon.querySelectorAll(".weaponCheck"));
const $weaponsCheckLabels = Array.from($filterBuildWeapon.querySelectorAll(".weaponCheckLabel"));
const $filterBuildType = $q("#filterBuildType");

const $allWeaponText = $q("#allWeaponText");
const allWeaponText = $allWeaponText.innerText;

window.selectedWeapon = [];
let url = new URL(window.location.href);

/**
 * Refresh with filter
 */
const sendForm = () => {
  $filterBuildSearch.value != "" ? url.searchParams.set("q", $filterBuildSearch.value) : url.searchParams.delete("q");
  $filterBuildType.value != 0 ? url.searchParams.set("t", $filterBuildType.value) : url.searchParams.delete("t");
  let ids = window.selectedWeapon.filter((id) => id);
  ids.length ? url.searchParams.set("w", ids.join(",")) : url.searchParams.delete("w");
  url.searchParams.delete("page");
  window.location.href = url.href;
};

/**
 * Update weapons label
 */
const updateWeapon = () => {
  let checkedWeapon = $weaponsChecks.filter(($w) => $w.checked);
  for (let i = 0; i < 2; i++) window.selectedWeapon[i] = checkedWeapon[i]?.dataset.id ?? null;
  window.selectedWeapon = window.selectedWeapon;
  if (checkedWeapon.length > 0) {
    let checkedWeaponid = checkedWeapon.map(($w) => $w.dataset.id);
    let weaponText = $weaponsCheckLabels.filter(($wl) => checkedWeaponid.includes($wl.dataset.id)).map(($wl) => $wl.innerText);
    $allWeaponText.innerText = weaponText.join(", ").replaceAll("\n", "").replaceAll("\t", "");
    $allWeaponCheck.disabled = false;
    $allWeaponCheck.checked = false;
    if (checkedWeapon.length == 1) $weaponsChecks.forEach(($w) => ($w.disabled = false));
    else
      $weaponsChecks.forEach(($w) => {
        if (!checkedWeapon.includes($w)) $w.disabled = true;
      });
  } else {
    $allWeaponText.innerText = allWeaponText;
    $allWeaponCheck.disabled = true;
    $allWeaponCheck.checked = true;
  }
};

/**
 * Set filter input at startup
 */
(() => {
  $filterBuildSearch.value = url.searchParams.get("q");
  $filterBuildType.value = url.searchParams.get("t") ?? "0";
  if (url.searchParams.get("w")) {
    let weapon = url.searchParams.get("w").split(",");
    let index = 0;
    $weaponsChecks.forEach(($w) => {
      if (index == 2) return;
      if (weapon.includes($w.dataset.id)) {
        $w.checked = true;
      }
    });
    updateWeapon();
  }
})();

/**
 * Add a weapon
 */
$weaponsChecks.forEach(($w) => {
  $w.addEventListener("change", () => {
    updateWeapon();
  });
});

/**
 * All weapon
 */
$allWeaponCheck.addEventListener("change", () => {
  if ($allWeaponCheck.checked) {
    $weaponsChecks.forEach(($w) => {
      $w.checked = false;
      $w.disabled = false;
    });
    window.selectedWeapon = window.selectedWeapon.map((id) => null);
    $allWeaponCheck.disabled = true;
    updateWeapon();
  }
});

/**
 * Filtering
 */
$filterBuildForm.addEventListener("submit", (event) => {
  event.preventDefault();
  sendForm();
});

/**
 * Reseting
 */
$filterBuildReset.addEventListener("click", (e) => {
  e.preventDefault();
  url.search = "";
  window.location.href = url.href;
});
