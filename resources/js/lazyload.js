import LazyLoad from "vanilla-lazyload";

// 创建懒加载实例
const lazyLoadInstance = new LazyLoad({
    elements_selector: ".lazy",
    use_native: true,
    threshold: 50,
    callback_error: (element) => {
        element.src = '/placeholder.jpg';
    }
});

// 导出更新方法
window.updateLazyLoad = () => {
    lazyLoadInstance.update();
};

export default lazyLoadInstance;
