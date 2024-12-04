import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
    static targets = [
        "toolCard",
        "filterBtn",
        "searchInput",
        "categorySearch",
        "activeFilters",
        "dropdown",
        "dropdownLabel",
        "selectedCount",
        "menu"
    ]

    static values = {
        activeFilters: { type: Array, default: ['all'] }
    }

    connect() {
        console.log('Tools filter controller connected');
        this.searchTerm = ''
        this.initializeCards()
        this.updateActiveFilters()

        // Fermer le menu au clic en dehors
        document.addEventListener('click', (e) => {
            if (!this.dropdownTarget.contains(e.target) && !this.menuTarget.contains(e.target)) {
                this.menuTarget.classList.add('hidden')
            }
        })
    }

    toggleDropdown() {
        this.menuTarget.classList.toggle('hidden')
    }

    search(e) {
        this.searchTerm = e.target.value.toLowerCase()
        this.filterTools()
    }

    searchCategory(e) {
        const searchValue = e.target.value.toLowerCase()
        const categoryButtons = this.menuTarget.querySelectorAll('.filter-btn:not([data-category="all"])')

        categoryButtons.forEach(btn => {
            const categoryName = btn.textContent.trim().toLowerCase()
            btn.style.display = categoryName.includes(searchValue) ? 'flex' : 'none'
        })
    }

    toggleFilter(e) {
        const category = e.currentTarget.dataset.category

        if (category === 'all') {
            this.clearAllFilters()
        } else {
            this.activeFiltersValue = this.activeFiltersValue.filter(f => f !== 'all')
            e.currentTarget.classList.toggle('active')

            if (e.currentTarget.classList.contains('active')) {
                this.activeFiltersValue = [...this.activeFiltersValue, category]
            } else {
                this.activeFiltersValue = this.activeFiltersValue.filter(f => f !== category)
            }

            if (this.activeFiltersValue.length === 0) {
                this.resetToAllFilters()
            }
        }

        this.updateActiveFilters()
        this.filterTools()
        this.menuTarget.classList.add('hidden')
    }

    removeFilter(e) {
        const filter = e.currentTarget.dataset.remove
        this.activeFiltersValue = this.activeFiltersValue.filter(f => f !== filter)
        this.element.querySelector(`[data-category="${filter}"]`).classList.remove('active')

        if (this.activeFiltersValue.length === 0) {
            this.resetToAllFilters()
        }

        this.filterTools()
        this.updateActiveFilters()
    }

    clearAllFilters() {
        this.filterBtnTargets.forEach(btn => btn.classList.remove('active'))
        this.resetToAllFilters()
        this.filterTools()
        this.updateActiveFilters()
    }

    // Méthodes privées
    initializeCards() {
        this.toolCardTargets.forEach(card => {
            card.style.transition = 'opacity 0.2s ease-in-out, transform 0.2s ease-in-out'
            card.style.opacity = '1'
            card.style.transform = 'translateY(0)'
        })
    }

    filterTools() {
        this.toolCardTargets.forEach(card => {
            const cardCategories = card.dataset.categories.split(' ')
            const cardName = card.dataset.name
            const cardDescription = card.dataset.description

            const matchesSearch = this.searchTerm === '' ||
                cardName.includes(this.searchTerm) ||
                cardDescription.includes(this.searchTerm) ||
                cardCategories.some(cat => cat.includes(this.searchTerm))

            const matchesFilter = this.activeFiltersValue.includes('all') ||
                this.activeFiltersValue.some(filter => cardCategories.includes(filter))

            this.toggleCardVisibility(card, matchesSearch && matchesFilter)
        })
    }

    toggleCardVisibility(card, shouldDisplay) {
        if (shouldDisplay) {
            card.style.display = 'block'
            setTimeout(() => {
                card.style.opacity = '1'
                card.style.transform = 'translateY(0)'
            }, 10)
        } else {
            card.style.opacity = '0'
            card.style.transform = 'translateY(20px)'
            setTimeout(() => card.style.display = 'none', 200)
        }
    }

    resetToAllFilters() {
        this.activeFiltersValue = ['all']
        this.element.querySelector('[data-category="all"]').classList.add('active')
    }

    updateSelectedCount() {
        const count = this.activeFiltersValue.includes('all') ? 0 : this.activeFiltersValue.length
        this.selectedCountTarget.textContent = count > 0 ? count : ''

        if (count > 0) {
            const selectedCategories = this.activeFiltersValue
            this.dropdownLabelTarget.textContent = selectedCategories.slice(0, 2).join(', ') +
                (count > 2 ? ` (+${count - 2})` : '')
        } else {
            this.dropdownLabelTarget.textContent = 'Filtrer par catégories'
        }
    }

    updateActiveFilters() {
        // Mise à jour du conteneur de filtres actifs
        this.activeFiltersTarget.innerHTML = ''

        if (!this.activeFiltersValue.includes('all')) {
            this.activeFiltersValue.forEach(filter => {
                const badge = document.createElement('div')
                badge.className = 'inline-flex items-center bg-indigo-100 text-indigo-800 rounded-full px-3 py-1'
                badge.innerHTML = `
                    ${filter}
                    <button 
                        class="ml-2 text-indigo-600 hover:text-indigo-800"
                        data-action="click->tools-filter#removeFilter"
                        data-remove="${filter}"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                `
                this.activeFiltersTarget.appendChild(badge)
            })
        }

        // Mise à jour du compteur et du label
        this.updateSelectedCount()
    }
}