import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static targets = ['metric']
  static values = { url: String }

  connect() {
    this.element.addEventListener(
      'period:change',
      this.onPeriodChange
    )
  }

  disconnect() {
    this.element.removeEventListener(
      'period:change',
      this.onPeriodChange
    )
  }

  onPeriodChange = async (event) => {
    const params = event.detail.toString()
    await this.load(params)
  }

  async load(params) {
    if (!this.urlValue) return

    const response = await fetch(`${this.urlValue}?${params}`)
    const data = await response.json()

    this.render(data)
  }

  render(data) {
    this.metricTargets.forEach(metricEl => {
      const key = metricEl.dataset.metricKey
      const metric = data[key]

      if (!metric) return
      this.renderMetric(metricEl, metric)
    })
  }

  renderMetric(container, metric) {
    const valueEl = container.querySelector('.metric-value')
    const evolutionEl = container.querySelector('.metric-evolution')
    const percentEl = evolutionEl.querySelector('.percent')

    valueEl.textContent = metric.count
    percentEl.textContent = `${metric.evolutionPercent}%`

    evolutionEl.classList.remove(
      'metric-evolution--up',
      'metric-evolution--down',
      'metric-evolution--neutral'
    )

    evolutionEl.classList.add(
      metric.trend === 'up'
        ? 'metric-evolution--up'
        : metric.trend === 'down'
        ? 'metric-evolution--down'
        : 'metric-evolution--neutral'
    )
  }
}
