import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
    static targets = ["articleCard", "searchInput"]

    connect() {
        console.log('Articles filter controller connected')
    }

    search(e) {
        const searchTerm = e.target.value.toLowerCase()

        this.articleCardTargets.forEach(card => {
            const feedName = card.dataset.feedName.toLowerCase()
            const title = card.dataset.title.toLowerCase()

            const isVisible = feedName.includes(searchTerm) || title.includes(searchTerm)

            this.toggleCardVisibility(card, isVisible)
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
}