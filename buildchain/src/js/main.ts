import '@/css/app.css';

if (Craft.OpeningHours == null) Craft.OpeningHours = {} as typeof Craft.OpeningHours;

Craft.OpeningHours.Input = Garnish.Base.extend({
    id: 0,
    instanceMethod() {
        console.log(this.id);
    },
    init(id: number) {
        this.id = id;
    }
}, {
    test: "Hello, Alyx!",
    staticMethod() {
        console.log(this.test);
    }
})

Craft.OpeningHours.Input.staticMethod();
const e = new Craft.OpeningHours.Input(10);
e.instanceMethod();

if (import.meta.hot) {
  import.meta.hot.accept();
}
