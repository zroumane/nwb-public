import "../css/CraftDashboard.scss";
import Modal from "bootstrap/js/dist/modal";
import { $q, $qa } from "./Global";
import { getMethod } from "./Utils";

const $entityModal = $q("#entityModal");
let entityModal = new Modal($entityModal, {});

const $itemTemplate = $q("#itemTemplate");
const $itemSection = $q("#itemSection");
const $itemUl = $q("#itemUl");
const $addItemBtn = $q("#addItemBtn");

const $categoryTemplate = $q("#categoryTemplate");
const $categorySection = $q("#categorySection");
const $addCategoryBtn = $q("#addCategoryBtn");

window.currentCategory = [null, null];
window.currentDrag = [null, null];

const candrop = {
  items($moveDiv) {
    if (window.currentDrag[0] == "item_categories") return false;
    if ($moveDiv.dataset.position == window.currentDrag[1].position) return false;
    return true;
  },
  item_categories($moveDiv) {
    if (window.currentDrag[0] == "items" && $moveDiv.dataset.under) {
      if (window.currentDrag[1].category.split("/").reverse()[0] == $moveDiv.dataset.under) return false;
      else return true;
    }
    let draggedParentId = window.currentDrag[1].parent ? window.currentDrag[1].parent.split("/").reverse()[0] : null;
    let rawTargetTree = $moveDiv.parentElement.dataset?.tree;
    let targetTree = rawTargetTree ? rawTargetTree.split("-") : [null];
    if (targetTree.includes(window.currentDrag[1].id.toString())) return false;
    if (draggedParentId == targetTree.reverse()[0]) if (window.currentDrag[1].position == $moveDiv.dataset.position || window.currentDrag[1].position + 1 == $moveDiv.dataset.position) return false;
    return true;
  },
};

const loadEntity = {
  async items() {
    $itemSection.classList.add("d-none");
    $itemSection.firstElementChild.firstElementChild.innerText = "";
    $itemUl.innerHTML = "";
    if (window.currentCategory[1]) {
      $itemUl.appendChild($makeItemMove(null));
      let items = (await getMethod(`/api/item_categories/${window.currentCategory[1].id}/items`))["hydra:member"];
      items.forEach((item) => {
        let $item = $itemTemplate.content.cloneNode(true).firstElementChild;
        $item.children[0].src = `/img/items/${item.itemKey.toLowerCase()}.png`;
        $item.children[1].innerText = item.itemKey;
        $item.children[1].addEventListener("dragstart", () => (window.currentDrag = ["items", item]));
        let $actions = $item.children;
        $actions[2].addEventListener("click", () => {
          loadentityModal("items", item);
        });
        $actions[3].addEventListener("click", async () => {
          if (window.confirm(`Delete ${item.itemKey} ?`)) {
            fetch(`/api/items/${item.id}`, { method: "DELETE" }).then(() => {
              loadEntity.items();
            });
          }
        });
        $itemUl.appendChild($item);
        $itemUl.appendChild($makeItemMove(item));
      });
      $itemSection.firstElementChild.firstElementChild.innerText = `${items.length} item from ${window.currentCategory[1].category}`;
      $itemSection.classList.remove("d-none");
    }
  },
  async item_categories() {
    window.currentCategory = [null, null];
    loadEntity.items();
    $categorySection.innerHTML = "";
    const categories = (await getMethod("/api/item_categories"))["hydra:member"];
    categories.forEach((category) => $makeCategory(category));
  },
};

/**
 * @param {HTMLElement} $moveDiv
 */
const setMoveListener = (type, $moveDiv) => {
  $moveDiv.addEventListener("dragover", (event) => event.preventDefault());
  $moveDiv.addEventListener("dragenter", () => {
    if (!candrop[type]($moveDiv)) return;
    $moveDiv.style.backgroundColor = "white";
    $moveDiv.parentElement.style.opacity = "0.7";
  });
  $moveDiv.addEventListener("dragleave", () => {
    $moveDiv.style.backgroundColor = "";
    $moveDiv.parentElement.style.opacity = "1";
  });
  $moveDiv.addEventListener("drop", (event) => {
    event.preventDefault();
    $moveDiv.style.backgroundColor = "";
    $moveDiv.parentElement.style.opacity = "1";
    if (!candrop[type]($moveDiv)) return;

    let body = new Object();
    let confirmation = `Move `;

    if (window.currentDrag[0] == "items") {
      confirmation += window.currentDrag[1].itemKey;
      let category = $moveDiv.dataset?.under;
      if (category) {
        body.category = `/api/item_categories/${category}`;
        confirmation += ` under category ${category}`;
      }
      if ($moveDiv.parentElement.id == "itemUl") {
        body.position = parseInt($moveDiv.dataset.position);
        confirmation += ` at position ${body.position}`;
      }
    } else {
      confirmation += window.currentDrag[0].category;
      let parentId = $moveDiv.parentElement.dataset?.id;
      body.parent = parentId ? `/api/item_categories/${parentId}` : null;
      confirmation += parentId ? ` under category ${parentId}` : ` to root`;
      body.position = parseInt($moveDiv.dataset.position);
      confirmation += ` at position ${body.position}`;
    }

    if (window.confirm((confirmation += " ?"))) {
      fetch(`/api/${window.currentDrag[0]}/${window.currentDrag[1].id}`, {
        headers: { "Content-Type": "application/json" },
        method: "PUT",
        body: JSON.stringify(body),
      }).then(() => (window.currentDrag[0] == "items" ? loadEntity.items() : loadEntity.item_categories()));
    }
  });
};

/**
 * @param {Object} category
 * @param {HTMLLIElement} $category
 */
const setCurrentCategory = ($category, category) => {
  if (window.currentCategory[0]) window.currentCategory[0].firstElementChild.firstElementChild.style.backgroundColor = "";
  window.currentCategory[0] = $category;
  window.currentCategory[1] = category;
  $category.firstElementChild.firstElementChild.style.backgroundColor = "white";
  loadEntity.items();
};

const $makeItemMove = (item) => {
  let $itemMoveDiv = document.createElement("div");
  $itemMoveDiv.dataset.position = item ? item.position : 0;
  $itemMoveDiv.classList.add("itemMoveDiv");
  setMoveListener("items", $itemMoveDiv);
  return $itemMoveDiv;
};

const loadentityModal = (type, entity) => {
  entityModal.hide();
  let $modalTitle = $entityModal.querySelector(".modal-title");
  $modalTitle.innerText = `${entity ? "Edit" : "Create"} ${type == "items" ? "item" : "category"} ${entity ? entity.id : ""}`;
  let $modalInput = $entityModal.querySelector('input[type="text"]');
  $modalInput.value = entity ? (type == "items" ? entity.itemKey : entity.category) : "";
  let $sendBtn = $entityModal.querySelector("button.btn-primary");
  $sendBtn.lastElementChild.innerText = entity ? "Edit" : "Create";
  $sendBtn.onclick = null;
  $sendBtn.onclick = () => {
    $sendBtn.classList.add("disabled");
    $sendBtn.firstElementChild.classList.remove("d-none");
    let body = {
      position: !entity ? -1 : entity.position < 0 ? -1 : entity.position,
    };
    if (type == "items") {
      body.category = "/api/item_categories/" + window.currentCategory[1].id;
      body.itemKey = $modalInput.value ?? "new_items";
    } else {
      body.category = $modalInput.value ?? "new_category";
    }

    fetch(`/api/${type}${entity ? `/${entity.id}` : ""}`, {
      headers: { "Content-Type": "application/json" },
      method: entity ? "PUT" : "POST",
      body: JSON.stringify(body),
    }).then(() => {
      loadEntity[type]().then(() => {
        entityModal.hide();
        $sendBtn.classList.remove("disabled");
        $sendBtn.firstElementChild.classList.add("d-none");
      });
    });
  };
  entityModal.show();
};

const $makeCategoryMove = ($parent, position) => {
  let $moveCategoryDiv = document.createElement("div");
  $moveCategoryDiv.classList.add("categoryMoveDiv");
  $moveCategoryDiv.dataset.position = position;
  if ($parent.children.length == 0) $moveCategoryDiv.dataset.under = $parent.dataset?.id ?? 0;
  setMoveListener("item_categories", $moveCategoryDiv);
  $parent.appendChild($moveCategoryDiv);
};

/**
 * @param {Object} category
 * @param {HTMLElement} $parent
 */
const $makeCategory = (category, $parent) => {
  let $category = $categoryTemplate.content.cloneNode(true).firstElementChild;
  let $categoryLabel = $category.firstElementChild.firstElementChild;
  $categoryLabel.addEventListener("dragstart", () => (window.currentDrag = ["item_categories", category]));
  $categoryLabel.innerText = category.category;
  $categoryLabel.onclick = () => setCurrentCategory($category, category);

  let $actions = $category.firstElementChild.children;
  $actions[2].addEventListener("click", () => {
    loadentityModal("item_categories", category);
  });
  $actions[3].addEventListener("click", async () => {
    if (category.children.length != 0) {
      return window.alert("Before deleting this category, you have to move or delete its children.");
    }
    if (window.confirm(`Delete ${category.category}`)) {
      fetch(`/api/item_categories/${category.id}`, { method: "DELETE" }).then(() => {
        loadEntity.item_categories();
      });
    }
  });

  let parentTree = $parent?.dataset.tree;
  let categoryTree = parentTree ? (parentTree += `-${category.id}`) : category.id.toString();
  $category.lastElementChild.dataset.tree = categoryTree;
  $category.lastElementChild.dataset.id = category.id;

  if ($parent && $parent.children.length === 0) $makeCategoryMove($parent, 0);
  if ($category.lastElementChild.children.length === 0) $makeCategoryMove($category.lastElementChild, 0);
  if ($categorySection.children.length == 0) $makeCategoryMove($categorySection, 0);

  $parent ? $parent.appendChild($category) : $categorySection.appendChild($category);
  $makeCategoryMove($parent ?? $categorySection, category.position + 1);
  category.children.forEach((c) => $makeCategory(c, $category.lastElementChild));
};

(() => {
  loadEntity.item_categories();
  $addCategoryBtn.addEventListener("click", () => loadentityModal("item_categories"));
  $addItemBtn.addEventListener("click", () => loadentityModal("items"));
})();
