declare namespace Craft.OpeningHours {
  interface Settings {
    periods: unknown[];
  }

  interface Instance {
    container: JQuery<HTMLElement>;
    namespacedId: string;
    settings: Settings;
    currentRowsAmount: number;
    addPeriodButton: JQuery<HTMLElement>;

    addRow(this): void;
    deleteRow(this, e: JQuery.ClickEvent<HTMLElement, undefined>): void;
    getNewRowHTML(this): string;
    updateAddPeriodButton(this): void;
  }

  interface InputConstructor extends BaseClass<Instance, object> {
    new (namespacedId: string, id: string, settings: string): Instance;
  }

  let Input: InputConstructor;
}
