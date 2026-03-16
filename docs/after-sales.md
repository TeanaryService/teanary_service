## 售后（退换货）功能说明

本文档说明当前系统中售后（退换货）的整体设计与处理流程，方便产品、客服和开发统一理解。

---

## 一、数据结构与模型关系

- `after_sales` 表（主键 Snowflake `id`）
  - 关联字段：
    - `order_id`：关联订单 `orders.id`
    - `order_item_id`：关联订单明细 `order_items.id`
    - `product_id`：关联商品 `products.id`
    - `warehouse_id`：关联仓库 `warehouses.id`
    - `user_id`：发起售后的前台用户
  - 业务字段：
    - `type`：售后类型
      - `refund_only`：仅退款
      - `refund_and_return`：退货退款
      - `exchange`：换货
    - `status`：售后状态
      - `pending`：待审核
      - `approved`：已通过
      - `rejected`：已拒绝
      - `in_return`：退货中
      - `completed`：已完成
      - `canceled`：已取消
    - `reason`：用户填写的售后理由（前后台都可见）
    - `description`：用户填写的详细说明（前后台都可见）
    - `quantity`：售后数量（件数）
    - `refund_amount`：申请退款金额（预留）
    - `exchange_product_id`：换货目标商品 ID（预留）
    - `images`：凭证图片（JSON，预留对接现有上传/Media）
    - `remarks`：后台备注（仅后台使用，用于记录审核说明、内部备注）
    - `logistics_company` / `tracking_number`：退货物流信息（预留）
    - `processed_at`：最后处理时间
  - 通用字段：
    - `created_at` / `updated_at`

- 模型关系
  - `App\Models\AfterSale`
    - `order()` → `belongsTo(Order::class)`
    - `orderItem()` → `belongsTo(OrderItem::class)`
    - `product()` → `belongsTo(Product::class)`
    - `exchangeProduct()` → `belongsTo(Product::class, 'exchange_product_id')`
    - `warehouse()` → `belongsTo(Warehouse::class)`
    - `user()` → `belongsTo(User::class)`
  - `App\Models\Order`
    - `afterSales()` → `hasMany(AfterSale::class)`
  - `App\Models\Product`
    - `afterSales()` → `hasMany(AfterSale::class)`
  - `App\Models\Warehouse`
    - `afterSales()` → `hasMany(AfterSale::class)`

---

## 二、订单状态与售后状态的关系

订单使用 `App\Enums\OrderStatusEnum`，在原有基础上增加了两个状态：

- `after_sale` (`OrderStatusEnum::AfterSale`)：售后处理中
- `after_sale_done` (`OrderStatusEnum::AfterSaleCompleted`)：售后完成

状态联动规则由 `App\Services\AfterSaleService` 负责：

1. **创建售后单（create）**
   - 新增一条 `after_sales` 记录，`status = pending`。
   - 若订单当前状态不是「售后处理中 / 售后完成」，则将订单状态更新为 `after_sale`。

2. **审核售后单（review）**
   - 仅允许在 `pending` 状态下审核。
   - 审核通过：
     - `after_sales.status = approved`
     - 订单状态更新为 `after_sale`（确保进入「售后处理中」）
   - 审核拒绝：
     - `after_sales.status = rejected`
     - 不强制修改订单状态（保持当前逻辑）。

3. **完成售后（complete）**
   - 仅允许在 `approved` / `in_return` 状态下完成。
   - `after_sales.status = completed`
   - 订单状态更新为 `after_sale_done`。
   - 预留 TODO：在此对接实际退款、换货发货逻辑（支付网关、发货服务等）。

4. **取消售后（cancel）**
   - 允许在 `pending` / `approved` / `in_return` 状态下取消。
   - `after_sales.status = canceled`
   - 若订单当前状态为 `after_sale`，则将订单状态恢复为 `completed`（可根据业务再调整）。

---

## 三、前台用户侧流程

### 1. 展示入口

- 入口页面：
  - 个人中心 → `我的订单` → 订单详情：`App\Livewire\Users\OrderDetail`
  - 视图：`resources/views/livewire/users/order-detail.blade.php`

- 订单详情页：
  - 顶部展示订单基本信息和状态（含「售后处理中 / 售后完成」）。
  - 左侧「商品信息」区域中，每个订单商品行右侧在 **订单状态满足条件** 时展示「申请售后」按钮。

### 2. 申请条件

在前台 `OrderDetail` 组件中，`submitAfterSale()` 做了状态校验：

- 仅当订单状态为以下之一时允许申请售后：
  - `paid`（已付款）
  - `shipped`（已发货）
  - `completed`（已完成）

### 3. 用户填写内容

在订单详情右侧新增了「售后申请信息」表单块：

- 字段（绑定到 `App\Livewire\Users\OrderDetail`）：
  - `afterSaleReason`：售后理由（简短文本）
  - `afterSaleDescription`：详细说明（多行文本）
- 说明：
  - 这两部分内容会和具体商品行一起提交，写入 `after_sales.reason` 与 `after_sales.description`。
  - **前后台均可查看**，用于双方沟通问题。

### 4. 提交逻辑

前台组件：`App\Livewire\Users\OrderDetail::submitAfterSale(int $orderItemId)`

核心逻辑简述：

- 校验当前订单存在且属于当前用户。
- 校验订单状态是否允许申请售后。
- 调用 `AfterSaleService::create()`：
  - 传入 `order_id`、`order_item_id`、`user_id`、`warehouse_id`、`type`（目前默认“仅退款”）、`reason`、`description`、`quantity` 等。
  - Service 内部会校验数量不能超过订单行购买数量，并自动补全 `product_id`。
- 重置前台表单字段。
- 通过 Livewire 事件提示「售后申请已提交，等待客服审核」。

### 5. 用户查看售后记录

- 在订单详情页底部新增了「售后记录」区块：
  - 数据来源：`$order->afterSales()->latest()->get()`
  - 每条记录展示：
    - 售后单号
    - 类型（仅退款 / 退货退款 / 换货）
    - 当前状态
    - 申请时间
    - 用户填写的理由 (`reason`)
    - 用户填写的详细说明 (`description`)
  - 目的：让用户清楚了解哪些售后已发起、当前处理到哪一步。

---

## 四、后台 Manager 管理流程

### 1. 入口与列表

- 入口位置：
  - Manager 后台 → 左侧侧边栏「订单管理」分组 → 「售后管理」
  - Livewire 组件：`App\Livewire\Manager\AfterSaleList`
  - 路由：`/manager/after-sales` (`manager.after-sales`)
  - 视图：`resources/views/livewire/manager/after-sales.blade.php`

- 列表功能：
  - 筛选：
    - 关键字（售后 ID / 订单号 / 用户名 / 邮箱 / reason）
    - 售后类型（仅退款 / 退货退款 / 换货）
    - 状态（待审核 / 已通过 / 已拒绝 / 退货中 / 已完成 / 已取消）
  - 列信息：
    - 售后单 ID
    - 订单号
    - 用户信息（名称 + 邮箱）
    - 商品（当前使用 `product.slug` 展示，可按需替换成多语言名称）
    - 类型
    - 状态
    - 数量
    - 申请时间

### 2. 后台备注（仅后台可见）

在筛选区域下方，增加了一个后台备注输入框：

- 绑定字段：`App\Livewire\Manager\AfterSaleList::$remarks`
- 说明：
  - 在点击「通过 / 拒绝 / 标记完成 / 取消」任一操作时，当前 `remarks` 内容会一并传给 `AfterSaleService`。
  - Service 内部会将新备注 append 到 `after_sales.remarks`（与原有备注合并），用于记录后台处理说明与沟通记录。
  - **仅后台使用**，前台页面不会展示该字段。

### 3. 审核与处理操作

所有操作均通过 Livewire 方法完成，不再使用传统 Controller 路由：

- 组件方法：
  - `approve(int $id)`：通过
  - `reject(int $id)`：拒绝
  - `complete(int $id)`：标记完成
  - `cancel(int $id)`：取消售后
- 每个方法都会：
  - 根据 ID 查找 `AfterSale`。
  - 调用 `AfterSaleService` 对应方法，并传入当前 `remarks`。
  - 重置 `remarks`、刷新分页、通过 Livewire 事件触发全局成功提示。

### 4. 后台审核界面上的文案多语言

- 后台售后管理所有文字（标题、表头、按钮、状态、类型、空状态、后台备注说明）已抽离到语言包：
  - `__('manager.after_sales.*')`
- 支持的语言目录：
  - `lang/zh_CN/manager/after_sales.php`
  - `lang/en/manager/after_sales.php`
  - `lang/ja/manager/after_sales.php`
  - `lang/fr/manager/after_sales.php`
  - `lang/de/manager/after_sales.php`
  - `lang/es/manager/after_sales.php`
  - `lang/ru/manager/after_sales.php`
  - `lang/ko/manager/after_sales.php`

---

## 五、典型业务流程示例

### 场景一：用户申请仅退款

1. 用户在前台看到订单状态为「已完成」，发现商品有问题，在订单详情页填写：
   - 售后理由：「商品有质量问题」
   - 详细说明：「收到的商品外壳破裂，影响使用」
2. 在对应商品行点击「申请售后」。
3. 系统创建一条 `after_sales` 记录：
   - `type = refund_only`
   - `status = pending`
   - `reason` / `description` 保存用户填写内容。
   - 订单状态更新为 `after_sale`（售后处理中）。
4. 后台客服在「售后管理」中看到该记录，结合理由与说明进行审核：
   - 若通过：
     - 将状态更新为 `approved`（后续可根据需要再「标记完成」）。
   - 若确认退款完成并点击「标记完成」：
     - 售后状态更新为 `completed`。
     - 订单状态更新为 `after_sale_done`。

### 场景二：后台拒绝或取消售后

1. 客服在后台填写「后台备注」，例如：
   - 「用户提供的视频显示商品使用不当，不符合售后政策」。
2. 点击「拒绝」：
   - 售后状态更新为 `rejected`，备注会追加到 `after_sales.remarks` 中。
3. 若是已经通过但用户后面撤回请求，客服可以：
   - 在后台备注中写明原因。
   - 点击「取消」：
     - 售后状态为 `canceled`。
     - 若订单当前为 `after_sale`，则恢复为 `completed`。

---

## 六、后续可扩展点

1. **退款对接**：
   - 在 `AfterSaleService::complete()` 中对接支付网关退款逻辑，根据 `refund_amount` 实际发起退款。
2. **换货对接**：
   - 对 `type = exchange` 的售后，在完成前创建新订单或发货记录，走现有发货流程。
3. **退货入库与质检**：
   - 接入仓库模块，在退货确认后增加对应库存，并支持「良品 / 次品」等质检状态。
4. **前台展示后台反馈**：
   - 可选择在前台「售后记录」中增加只读字段，如「处理结果说明」，将后台 `remarks` 的部分内容（或单独字段）暴露给用户查看。

