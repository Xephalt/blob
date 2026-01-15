import { Controller } from '@hotwired/stimulus'
import * as echarts from 'echarts'

export default class extends Controller {
  static values = {
    data: Object
  }

  chart1 = null
  chart2 = null

  async connect() {
    await this.load()
    window.addEventListener('resize', this.onResize)
  }

  disconnect() {
    window.removeEventListener('resize', this.onResize)

    if (this.chart1) {
      this.chart1.dispose()
      this.chart1 = null
    }

    if (this.chart2) {
      this.chart2.dispose()
      this.chart2 = null
    }
  }

  onResize = () => {
    if (this.chart1) this.chart1.resize()
    if (this.chart2) this.chart2.resize()
  }

  async load(params = '') {
    const data = await this.getData(params)
    this.dataValue = data

    this.renderChart1()
    this.renderChart2()
  }

  /* -------------------------
   * CHART 1
   * Active users per day (bar)
   * Messages per day (line)
   * Avg response time per day (line)
   * ------------------------- */
  renderChart1() {
    const el = document.getElementById('chart')
    if (!el) {
      console.warn('[Chart1] DOM element #chart not found')
      return
    }

    if (this.chart1) {
      this.chart1.dispose()
    }

    this.chart1 = echarts.init(el)

    const d = this.dataValue

    this.chart1.setOption({
      tooltip: { trigger: 'axis' },
      toolbox: {
        show: true,
        right: 10,
        top: 10,
        feature: {
          saveAsImage: {
            show: true,
            title: 'Save as Image',
            name: 'chart-usage',
            pixelRatio: 2
          }
        }
      },
      legend: {
        type: 'scroll',
        bottom: 0
      },
      xAxis: {
        type: 'category',
        data: d.date
      },
      yAxis: [
        { type: 'value' },
        { type: 'value' }
      ],
      series: [
        {
          name: 'Active users per day',
          type: 'bar',
          data: d.activeUsersPerDay,
          color: '#2bf3b6'
        },
        {
          name: 'Messages per day',
          type: 'line',
          yAxisIndex: 0,
          data: d.messagesPerDay,
          color: '#005B50'
        },
        {
          name: 'Avg response time',
          type: 'line',
          yAxisIndex: 1,
          data: d.avgResponseTimePerDay,
          color: '#91a9dc'
        }
      ]
    })
  }

  /* -------------------------
   * CHART 2
   * Connection per day
   * Conversation per day
   * ------------------------- */
  renderChart2() {
    const el = document.getElementById('chart2')
    if (!el) {
      console.warn('[Chart2] DOM element #chart2 not found')
      return
    }

    if (this.chart2) {
      this.chart2.dispose()
    }

    this.chart2 = echarts.init(el)

    const d = this.dataValue

    this.chart2.setOption({
      tooltip: { trigger: 'axis' },
      toolbox: {
        show: true,
        right: 10,
        top: 10,
        feature: {
          saveAsImage: {
            show: true,
            title: 'Save as Image',
            name: 'chart-usage',
            pixelRatio: 2
          }
        }
      },
      legend: {
        type: 'scroll',
        bottom: 0
      },
      xAxis: {
        type: 'category',
        data: d.date
      },
      yAxis: {},
      series: [
        {
          name: 'Connection per day',
          type: 'line',
          data: d.connectionPerDay,
          color: '#91a9dc'
        },
        {
          name: 'Conversation per day',
          type: 'line',
          data: d.conversationPerDay,
          color: '#005B50'
        }
      ]
    })
  }

  /* -------------------------
   * DATA
   * ------------------------- */
  async getData(params = '') {
    const response = await fetch('/chart_data?' + params)
    return response.json()
  }

  /* -------------------------
   * UI EVENT
   * ------------------------- */
  onPeriodChange() {
    const form = document.getElementById('export-usage-data')
    if (!form) return

    const formData = new FormData(form)
    const params = new URLSearchParams(formData).toString()

    this.load(params)
  }
}
