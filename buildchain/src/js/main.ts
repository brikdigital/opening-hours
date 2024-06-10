import '@/css/app.css';

console.log('hi')

if (import.meta.hot) {
    import.meta.hot.accept();
}