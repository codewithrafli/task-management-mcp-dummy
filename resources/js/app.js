import Sortable from 'sortablejs';

// Expose SortableJS globally so Alpine's x-init in the kanban board can use it.
window.Sortable = Sortable;
