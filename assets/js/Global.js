const $q = document.querySelector.bind(document);
const $qa = document.querySelectorAll.bind(document);
const MAX_ROW = 6;
const MAX_COL = 5;

//recupere la lang envoyé par le php
const lang = $q("html").getAttribute("lang");
export { $qa, $q, MAX_ROW, MAX_COL, lang };
