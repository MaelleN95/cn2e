/* stimulusFetch: 'lazy' */
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    onChange() {
        const file = this.element.files[0];

        if (!file) {
            return;
        }

        const sanitizedName = this.sanitizeFilename(file.name);

        if (sanitizedName === file.name) {
            return;
        }

        const sanitizedFile = new File(
            [file],
            sanitizedName,
            {
                type: file.type,
                lastModified: file.lastModified,
            }
        );

        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(sanitizedFile);

        this.element.files = dataTransfer.files;

        console.log('Nom original :', file.name);
        console.log('Nom nettoyé :', sanitizedName);
    }

    sanitizeFilename(filename) {
        return filename
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/['’]/g, '-')
            .replace(/°/g, '')
            .replace(/[^a-zA-Z0-9._ -]/g, '')
            .replace(/\s+/g, ' ')
            .trim();
    }
}