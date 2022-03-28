import Popover from "bootstrap/js/dist/popover";

/**
 * @param {string} url
 * @returns []
 */
const getMethod = async (url) => {
  var data = await fetch(url, {
    cache: "no-store",
  });
  return await data.json();
};

const getBuildId = () => {
  let $buildId = document.querySelector("#buildId");
  return $buildId ? $buildId.value : null;
};

const setBrightness = ($skillContainer, skill) => {
  $skillContainer.style.filter = `brightness(${skill.selected ? 1 : 0.4})`;
};

const initCarCapsPopover = ($carCaps) => {
  $carCaps.forEach(($caps) => {
    $caps.forEach(($car, i) => {
      let key = `${$car.dataset.carkey}_Bonus_${(i + 1) * 50}_active`;
      new Popover($car, {
        content: window.skillLocal[key] ?? key,
        trigger: "hover",
        placement: "top",
        html: true,
      });
    });
  });
};

/**
 * Change skill popover
 * @param {HTMLElement} $skillContainer
 * @param {string} title
 * @param {string} description
 */
const changePopover = ({ el, skill, key, suffix }, init) => {
  el.dataset.alert = 0;
  let popover = Pop(el);
  if (!skill) return popover.disable();
  popover.enable();
  el.setAttribute("data-bs-original-title", window.skillLocal[skill.skillKey] ?? skill.skillKey);
  let description;
  if (key && el.dataset.alert == 0) {
    description = window.messageLocal[key] ?? key;
    if (suffix) description += suffix;
    el.dataset.alert = 1;
  } else {
    let desckey = skill.skillKey + "_description";
    description = window.skillLocal[desckey] ?? desckey;
    if (skill.cooldown) description += "<br><br>" + window.messageLocal["cooldown"] + `${skill.cooldown}s`;
  }
  el.setAttribute("data-bs-content", description);
  if (!init) popover.show();
};

/**
 * @param {HTMLElement} $el
 * @returns Popover instance
 */
const Pop = ($el) => Popover.getInstance($el);

export { getMethod, getBuildId, setBrightness, initCarCapsPopover, changePopover, Pop };
