import './stimulus_bootstrap.js';

// Désactiver turbo drive (chargement des assets de easy-admin problématique)
import "@hotwired/turbo";

window.Turbo.session.drive = false;