import LazyLoad from "vanilla-lazyload";

// 初始化懒加载
window.lazyLoadInstance = new LazyLoad({
    elements_selector: ".lazy",
    use_native: true, // 使用浏览器原生懒加载
    threshold: 50,    // 提前50px加载
    callback_error: (element) => {
        // 加载失败时使用默认图片
        element.src = '/placeholder.png';
    }
});
