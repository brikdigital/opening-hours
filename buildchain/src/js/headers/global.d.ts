declare namespace Craft.OpeningHours {
  interface Instance {
    instanceMethod(): void;
    id: number;
  }

  interface Static {
    test: string;
    staticMethod(): void;
  }

  interface InputConstructor extends Class<Instance, Static> {
    new(id: number): Instance;
  }

  let Input: InputConstructor;
}