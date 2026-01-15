import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
    static targets = ['checkbox', 'chips', 'inputs']

    toggle() {
        this.chipsTarget.innerHTML = ''
        this.inputsTarget.innerHTML = ''

        const checked = this.checkboxTargets.filter(cb => cb.checked)

        if (checked.length === 0) {
            this.renderPlaceholder()
            return
        }

        checked.forEach(cb => {
            this.renderChip(cb)
            this.renderHiddenInput(cb)
        })
    }

    renderPlaceholder() {
        const span = document.createElement('span')
        span.className = 'weekday-placeholder'
        span.textContent = 'Tous les jours'
        this.chipsTarget.appendChild(span)
    }

    renderChip(checkbox) {
        const label = checkbox.nextElementSibling.textContent.trim()
        const short = label.slice(0, 2)

        const chip = document.createElement('div')
        chip.className = 'weekday-chip'

        const text = document.createElement('span')
        text.textContent = short

        chip.appendChild(text)

        chip.addEventListener('click', (e) => {
            e.stopPropagation()
            checkbox.checked = false
            this.toggle()
            checkbox.dispatchEvent(
                new Event('change', { bubbles: true })
            )
        })

        this.chipsTarget.appendChild(chip)
    }

    renderHiddenInput(checkbox) {
        const input = document.createElement('input')
        input.type = 'hidden'
        input.name = 'weekday[]'
        input.value = checkbox.value
        this.inputsTarget.appendChild(input)
    }
}
