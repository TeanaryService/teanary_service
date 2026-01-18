# Teanary 系统架构与数据流程图

## 目录
- [系统整体架构](#系统整体架构)
- [数据流程图](#数据流程图)
  - [订单处理流程](#订单处理流程)
  - [多节点数据同步流程](#多节点数据同步流程)
  - [商品创建流程](#商品创建流程)
  - [支付处理流程](#支付处理流程)
  - [促销计算流程](#促销计算流程)

---

## 系统整体架构

```mermaid
graph TB
    subgraph "前端层 Frontend Layer"
        A[用户浏览器] --> B[Livewire 组件]
        B --> C[Blade 模板]
        C --> D[Tailwind CSS]
    end

    subgraph "应用层 Application Layer"
        B --> E[Livewire 组件]
        E --> F[Service 层]
        F --> G[Model 层]
        G --> H[Observer 层]
    end

    subgraph "业务服务层 Service Layer"
        F --> F1[ProductService<br/>商品服务]
        F --> F2[PromotionService<br/>促销服务]
        F --> F3[PaymentService<br/>支付服务]
        F --> F4[ShippingService<br/>物流服务]
        F --> F5[SyncService<br/>同步服务]
        F --> F6[MediaService<br/>媒体服务]
        F --> F7[CategoryService<br/>分类服务]
        F --> F8[CartService<br/>购物车服务]
        F --> F9[LocaleCurrencyService<br/>本地化服务]
    end

    subgraph "数据层 Data Layer"
        G --> I[(MySQL 数据库)]
        G --> J[(Redis 缓存)]
        G --> K[文件存储]
    end

    subgraph "后台管理 Admin Panel"
        L[Filament Manager<br/>管理员面板] --> F
        M[Filament User<br/>用户面板] --> F
    end

    subgraph "外部服务 External Services"
        F3 --> N[PayPal 支付网关]
        F5 --> O[远程节点 API]
        F9 --> P[Ollama AI<br/>翻译服务]
    end

    subgraph "队列系统 Queue System"
        H --> Q[队列任务]
        Q --> R[SyncBatchDataJob<br/>批量同步任务]
        Q --> S[ResizeUploadedImage<br/>图片处理任务]
        Q --> T[BatchWriteTrafficStatsJob<br/>流量统计任务]
    end

    style F fill:#e1f5ff
    style G fill:#fff4e1
    style I fill:#ffe1e1
    style J fill:#ffe1e1
    style Q fill:#e1ffe1
```

---

## 数据流程图

### 订单处理流程

```mermaid
sequenceDiagram
    participant User as 用户
    participant Cart as 购物车
    participant Checkout as 结算页面
    participant OrderService as 订单服务
    participant PromotionService as 促销服务
    participant ShippingService as 物流服务
    participant PaymentService as 支付服务
    participant Order as 订单模型
    participant Observer as 订单观察者
    participant Notification as 通知系统

    User->>Cart: 添加商品到购物车
    Cart->>Cart: 计算小计
    
    User->>Checkout: 进入结算页面
    Checkout->>Checkout: 加载收货地址
    Checkout->>ShippingService: 获取可用物流方式
    ShippingService-->>Checkout: 返回物流选项
    
    User->>Checkout: 选择物流方式
    Checkout->>ShippingService: 计算物流费用
    ShippingService-->>Checkout: 返回费用
    
    Checkout->>PromotionService: 计算订单促销
    PromotionService->>PromotionService: 检查促销规则
    PromotionService-->>Checkout: 返回促销信息
    
    Checkout->>Checkout: 计算订单总价
    
    User->>Checkout: 提交订单
    Checkout->>OrderService: 创建订单
    OrderService->>Order: 保存订单数据
    Order->>Observer: 触发 created 事件
    Observer->>Notification: 发送订单创建通知
    
    OrderService-->>Checkout: 返回订单ID
    Checkout->>PaymentService: 创建支付
    PaymentService->>PaymentService: 选择支付网关
    PaymentService-->>Checkout: 返回支付URL
    Checkout->>User: 跳转到支付页面
```

### 多节点数据同步流程

```mermaid
sequenceDiagram
    participant Model as 数据模型
    participant Observer as 模型观察者
    participant Syncable as Syncable Trait
    participant SyncService as 同步服务
    participant SyncLog as 同步日志
    participant Queue as 队列系统
    participant SyncJob as 批量同步任务
    participant RemoteNode as 远程节点
    participant SyncController as 同步控制器

    Note over Model,RemoteNode: 数据变更触发同步

    Model->>Observer: 触发 created/updated/deleted
    Observer->>Syncable: 检查是否可同步
    Syncable->>SyncService: recordSync(模型, 操作, 节点)
    
    SyncService->>SyncService: 生成同步哈希
    SyncService->>SyncService: 准备同步数据
    SyncService->>SyncLog: 创建同步日志记录
    SyncLog->>SyncLog: 状态: pending
    
    SyncService->>Queue: 分发批量同步任务
    Queue->>SyncJob: 执行批量同步
    
    SyncJob->>SyncJob: 批量获取待同步记录
    SyncJob->>SyncJob: 按模型类型分组
    SyncJob->>SyncJob: 打包批量数据
    
    SyncJob->>RemoteNode: POST /api/sync/receive-batch
    Note over RemoteNode: API Key 验证
    RemoteNode->>SyncController: 接收批量数据
    SyncController->>SyncController: 验证数据完整性
    SyncController->>SyncController: 处理批量同步
    
    loop 处理每条记录
        SyncController->>SyncController: 检查是否跳过
        SyncController->>SyncController: 执行创建/更新/删除
        SyncController->>SyncController: 更新同步状态
    end
    
    SyncController-->>SyncJob: 返回同步结果
    SyncJob->>SyncLog: 更新同步状态
    SyncLog->>SyncLog: 状态: completed/failed
    
    alt 同步失败
        SyncJob->>SyncJob: 自动重试机制
    end
```

### 商品创建流程

```mermaid
flowchart TD
    A[Chrome 插件/API] --> B[ProductController]
    B --> C{验证请求}
    C -->|失败| D[返回错误]
    C -->|成功| E[开始事务]
    
    E --> F[ProductService.createProduct]
    F --> G[创建 Product 模型]
    G --> H[ProductObserver]
    H --> I{需要同步?}
    I -->|是| J[SyncService.recordSync]
    I -->|否| K[继续处理]
    
    F --> L[MediaService.handleMainImage]
    L --> M[上传主图]
    M --> N[创建 Media 记录]
    N --> O[MediaObserver]
    O --> P[SyncService.recordSync]
    
    F --> Q[CategoryService.findOrCreateCategory]
    Q --> R{分类存在?}
    R -->|否| S[创建分类]
    S --> T[CategoryObserver]
    T --> U[SyncService.recordSync]
    R -->|是| V[使用现有分类]
    
    F --> W[创建 ProductTranslation]
    W --> X[ProductTranslationObserver]
    X --> Y[SyncService.recordSync]
    
    F --> Z[创建 ProductVariant]
    Z --> AA[创建规格关联]
    
    F --> AB[提交事务]
    AB --> AC[返回成功]
    
    J --> AD[队列任务]
    P --> AD
    U --> AD
    Y --> AD
    AD --> AE[批量同步到远程节点]
    
    style F fill:#e1f5ff
    style J fill:#ffe1e1
    style AD fill:#e1ffe1
```

### 支付处理流程

```mermaid
stateDiagram-v2
    [*] --> 订单创建: 用户提交订单
    订单创建 --> 订单待支付: 订单状态: Pending
    
    订单待支付 --> 创建支付: 跳转到支付页面
    创建支付 --> 选择支付网关: PaymentService.createPayment
    
    选择支付网关 --> PayPal网关: 支付方式: PayPal
    选择支付网关 --> 其他网关: 支付方式: 其他
    
    PayPal网关 --> 跳转PayPal: 生成支付URL
    跳转PayPal --> 用户支付: 用户在PayPal完成支付
    
    用户支付 --> 支付成功回调: PayPal Webhook
    用户支付 --> 支付取消: 用户取消支付
    用户支付 --> 支付失败: 支付失败
    
    支付成功回调 --> 验证支付: 验证支付签名
    验证支付 --> 更新订单状态: 订单状态: Paid
    更新订单状态 --> 发送通知: 通知用户和管理员
    发送通知 --> [*]
    
    支付取消 --> 订单取消: 订单状态: Cancelled
    订单取消 --> [*]
    
    支付失败 --> 订单失败: 订单状态: Failed
    订单失败 --> [*]
```

### 促销计算流程

```mermaid
flowchart TD
    A[订单/商品] --> B[PromotionService]
    B --> C[获取可用促销]
    C --> D[从缓存获取所有促销]
    D --> E{缓存存在?}
    E -->|否| F[查询数据库]
    F --> G[缓存结果]
    G --> D
    E -->|是| H[过滤可用促销]
    
    H --> I[检查促销时间]
    I --> J[检查用户组]
    J --> K[检查商品关联]
    K --> L[返回可用促销列表]
    
    L --> M[遍历促销规则]
    M --> N[检查条件类型]
    
    N --> O{订单金额条件?}
    O -->|是| P[比较订单总金额]
    O -->|否| Q[比较订单数量]
    
    P --> R{满足条件?}
    Q --> R
    R -->|否| S[跳过此规则]
    R -->|是| T[计算折扣金额]
    
    T --> U{折扣类型?}
    U -->|固定金额| V[直接使用折扣值]
    U -->|百分比| W[计算百分比折扣]
    
    V --> X[应用折扣]
    W --> X
    X --> Y[比较最优折扣]
    Y --> Z[返回最终价格和促销信息]
    
    S --> AA{还有规则?}
    AA -->|是| M
    AA -->|否| Z
    
    style B fill:#e1f5ff
    style D fill:#fff4e1
    style T fill:#ffe1e1
```

---

## 核心组件说明

### 1. Service 层架构

```
Services/
├── ProductService          # 商品业务逻辑
├── PromotionService        # 促销计算逻辑
├── PaymentService          # 支付处理逻辑
├── ShippingService         # 物流计算逻辑
├── SyncService            # 数据同步逻辑
├── MediaService           # 媒体文件处理
├── CategoryService        # 分类管理
├── CartService            # 购物车管理
├── LocaleCurrencyService  # 本地化服务
└── Payments/
    ├── PaymentManager     # 支付网关管理器
    └── PaypalGateway      # PayPal 支付实现
```

### 2. Model 层架构

```
Models/
├── 核心业务模型
│   ├── Product            # 商品
│   ├── ProductVariant     # 商品规格
│   ├── Order              # 订单
│   ├── Cart               # 购物车
│   └── Promotion          # 促销
├── 关联模型
│   ├── ProductCategory    # 商品分类关联
│   ├── ProductAttributeValue  # 商品属性值
│   └── OrderItem          # 订单项
├── 翻译模型
│   ├── ProductTranslation
│   ├── CategoryTranslation
│   └── PromotionTranslation
└── 系统模型
    ├── SyncLog            # 同步日志
    ├── SyncStatus         # 同步状态
    └── TrafficStatistic   # 流量统计
```

### 3. Observer 层架构

```
Observers/
├── ProductObserver           # 商品变更监听
├── OrderObserver             # 订单变更监听
├── PromotionObserver         # 促销变更监听
├── CategoryObserver          # 分类变更监听
├── MediaObserver             # 媒体变更监听
└── [其他模型观察者]
```

### 4. 数据同步机制

- **触发机制**: 通过 Observer 监听模型事件
- **同步方式**: 批量异步同步，提升效率
- **冲突解决**: 以最新数据为准（基于时间戳）
- **文件同步**: 自动同步媒体文件和转换文件
- **重试机制**: 失败自动重试，确保数据不丢失

---

## 技术栈说明

### 后端技术
- **Laravel 12.x**: Web 框架
- **PHP 8.1+**: 服务器语言
- **MySQL 8.0+**: 主数据库
- **Redis**: 缓存和会话存储
- **Laravel Octane**: 高性能应用服务器

### 前端技术
- **Livewire 3.x**: 全栈框架
- **Tailwind CSS 3.x**: CSS 框架
- **Alpine.js**: 轻量级 JS 框架
- **Vite**: 前端构建工具

### 管理后台
- **Filament 3.x**: Laravel 管理面板

---

## 数据流向总结

1. **用户请求** → Livewire 组件 → Service 层 → Model 层 → 数据库
2. **数据变更** → Observer → SyncService → 队列 → 远程节点
3. **支付流程** → PaymentService → 支付网关 → Webhook → 订单更新
4. **促销计算** → PromotionService → 缓存查询 → 规则匹配 → 折扣计算

---

**文档版本**: 1.0  
**最后更新**: 2024
