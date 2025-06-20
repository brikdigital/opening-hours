import '@/css/app.css';

if (Craft.OpeningHours == null) Craft.OpeningHours = {} as typeof Craft.OpeningHours;

Craft.OpeningHours.Input = Garnish.Base.extend({
  container: null,
  namespacedId: null,
  settings: null,
  currentRowsAmount: null,
  addPeriodButton: null,

  init(namespacedId, id, settings) {
    this.container = $('.opening-hours-periods').first();
    this.namespacedId = namespacedId;
    this.settings = JSON.parse(settings);

    this.currentRowsAmount = this.settings.periods.length;

    this.addPeriodButton = $(`#${namespacedId}-addNewPeriodButton`).first();
    this.addPeriodButton.on('click', (e) => {
      e.preventDefault();
      this.addRow();
    });

    this.container.delegate( ".removePeriodButton", "click", (e) => {
      e.preventDefault();
      this.deleteRow(e);
    });

    // this.removePeriodButtons = $(`.removePeriodButton`);
    // this.removePeriodButtons.on('click', (e) => {
    //   e.preventDefault();
    //   this.deleteRow(e);
    // });
  },

  addRow() {
    this.currentRowsAmount++;

    const html = this.getNewRowHTML();
    const period = $(html).appendTo(this.container);

    this.trigger('blockAdded', {
      $block: period,
    });

    Craft.initUiElements(period);

    $('.datewrapper > input', period).datepicker(Craft.datepickerOptions);
    $('.timewrapper > input', period).timepicker(Craft.timepickerOptions);

    this.updateAddPeriodButton();
  },

  deleteRow(e) {
    $(e.target).closest(".opening-hours-field-period").remove();
  },

  getNewRowHTML() {
    const template = $(`template#${this.namespacedId}-placeholderPeriodData`).first().html();
    return template.replace(/___NEWINDEX___/g, this.currentRowsAmount);
  },

  updateAddPeriodButton() {
    const btn = this.addPeriodButton;
    btn.remove();
    this.container.append(btn);

    this.addPeriodButton = btn;
    this.addPeriodButton.on('click', (e: MouseEvent) => {
      e.preventDefault();
      this.addRow();
    });
  },
});

// HACK: For some godawful reason, I cannot get this AssetBundle JS to execute
//   *before* the one added with View#registerJs in OpeningHoursField.php:L357.
//   So let's do some dirty event-based code execution. :)
document.dispatchEvent(new CustomEvent('opening-hours-init'));

if (import.meta.hot) {
  import.meta.hot.accept();
}
