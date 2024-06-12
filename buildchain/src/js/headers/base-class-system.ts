// Type definitions for the Base.js class system from 2006
// Largely inspired by UCCTD's definitions for the Impact Class System, which is based on John Resig's "Simple JavaScript Inheritance" system from 2008.
// Thanks to Nax for helping with Base.js' extra `_static` argument support for `extend` calls

type ReplaceThisParameter<T, This2> = T extends (
    this: infer This,
    ...args: infer Args
) => infer Return ? unknown extends This ? T : (this: This2, ...args: Args) => Return : T;

type ClassMethodThis<
    K extends keyof Prototype,
    Constructor,
    Prototype,
    ParentPrototype,
> = K extends keyof ParentPrototype
    ? Prototype & { constructor: Constructor; parent: ParentPrototype }
    : Prototype & { constructor: Constructor }

type ClassMember<Key extends keyof Prototype, Constructor, Prototype, ParentPrototype> = ReplaceThisParameter<
    Prototype[Key],
    ClassMethodThis<Key, Constructor, Prototype, ParentPrototype>
>;

type ClassDefinition<Constructor, Prototype, ParentPrototype> = {
    [K in keyof Prototype]?: ClassMember<K, Constructor, Prototype, ParentPrototype> | null;
}

type ClassStaticMember<Key extends keyof Prototype, Prototype> = Prototype[Key] extends (
    this: infer This,
    ...args: infer Args
) => infer Return ? unknown extends This ? Prototype[Key] : (this: This, ...args: Args) => Return : Prototype[Key];

type ClassStaticDefinition<Prototype> = {
    [K in keyof Prototype]?: ClassStaticMember<K, Prototype> | null;
}

type ClassPrototype<Constructor, Instance, Static> = Constructor extends new (
        ...args: infer Args
    ) => Instance
    ? { [K in keyof Instance]: Instance[K] }
    & { [K in keyof Static]: Static[K] }
    & {
    init: (this: Instance & Static, ...args: Args) => void;
    staticInstantiate: (this: Instance & Static, ...args: Args) => (Instance & Static) | null | undefined
}
    : Instance;


interface InstanceClass<Instance, Static> {
    extend<ChildConstructor extends { prototype: unknown }>(
        this: this,
        instanceDefinition: ClassDefinition<
            ChildConstructor,
            ChildConstructor['prototype'],
            this['prototype']
        >,
        staticDefinition: ClassStaticDefinition<
            ChildConstructor['prototype']
        >
    ): ChildConstructor;
    readonly prototype: ClassPrototype<this, Instance, Static>;
}

type Class<Instance, Static> = InstanceClass<Instance, Static> & Static;