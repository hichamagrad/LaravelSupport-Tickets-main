import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

import * as FilePond from 'filepond';

window.FilePond = FilePond;

// Echo is already initialized in bootstrap.js
console.log('Laravel Echo should be initialized in bootstrap.js');
