import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        this.container =
            this.element.closest('.ea-vich-image')
            || this.element.closest('.field-image')
            || this.element.closest('.form-widget')
            || this.element.closest('.form-group')
            || this.element.parentElement;

        this.preview = this.container.querySelector('img');
        this.placeholder = this.container.querySelector('[data-preview-placeholder]');

        if (!this.preview) {
            this.preview = this.createPreview();
        }

        this.initialSrc = this.preview?.getAttribute('src') || null;

        this.applyClasses(this.preview);
        this.updatePlaceholder();
    }

    onChange() {
        const file = this.element.files[0];

        if (!file) {
            this.reset();
            return;
        }

        const reader = new FileReader();

        reader.onload = (e) => {
            this.preview.src = e.target.result;
            this.preview.style.display = 'block';
            if (this.placeholder) {
                this.placeholder.style.display = 'none';
            }
        };

        reader.readAsDataURL(file);
    }

    reset() {
        if (this.initialSrc) {
            this.preview.src = this.initialSrc;
            this.preview.style.display = 'block';
        } else {
            this.preview.src = '';
            this.preview.style.display = 'none';
        }

        this.updatePlaceholder();
    }

    createPreview() {
        const img = document.createElement('img');
        img.style.display = 'none';

        this.container.appendChild(img);
        this.applyClasses(img);

        return img;
    }

    applyClasses(img) {
        img.className = [
            'mt-2',
            'block',
            'w-full',
            'max-w-full',
            'h-auto',
            'object-contain',
            'max-h-64',
            'sm:max-h-72',
            'md:max-h-80',
            'lg:max-h-96',
            'min-w-0', 
            'shadow-none',
        ].join(' ');

        img.style.maxWidth = '100%';
        img.style.width = '100%';
        img.style.height = 'auto';
        img.style.display = (img.hasAttribute('src') && img.getAttribute('src')) ? 'block' : 'none';
        img.style.objectFit = 'contain';
    }

    updatePlaceholder() {
        if (!this.placeholder) {
            return;
        }

        if (this.preview && this.preview.hasAttribute('src') && this.preview.getAttribute('src')) {
            this.placeholder.style.display = 'none';
        } else {
            this.placeholder.style.display = 'block';
        }
    }
}