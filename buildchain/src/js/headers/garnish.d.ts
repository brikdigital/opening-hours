declare namespace Garnish {
  interface Base {
    readonly classId: number;
  }
  interface BaseConstructor extends Class<Base, object> {
    new(): Base;
  }
  let Base: BaseConstructor;
}
