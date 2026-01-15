import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  emit() {
    const formData = new FormData(this.element)
    const params = new URLSearchParams(formData)

    this.element.dispatchEvent(
      new CustomEvent('period:change', {
        bubbles: true,
        detail: params
      })
    )
  }
}
