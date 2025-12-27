# 测试说明文档

## 测试类型说明

### Unit 测试（单元测试）
**位置**: `tests/Unit/`

**用途**: 测试单个类或方法的功能，通常使用Mock来隔离依赖

**特点**:
- 测试速度快
- 不依赖外部资源（数据库、网络等）
- 专注于单个组件的逻辑
- 使用 `RefreshDatabase` trait 来重置数据库状态

**示例**:
```php
// 测试Model的关系
public function testUserRelationship()
{
    $order = new Order();
    $relation = $order->user();
    $this->assertInstanceOf(BelongsTo::class, $relation);
}

// 测试Service的方法
public function testCalculateVariantPrice()
{
    $service = new PromotionService();
    $result = $service->calculateVariantPrice($variant, 1);
    $this->assertEquals(100, $result['final_price']);
}
```

### Feature 测试（功能测试）
**位置**: `tests/Feature/`

**用途**: 测试完整的功能流程，包括HTTP请求、路由、控制器、中间件等

**特点**:
- 测试完整的用户流程
- 可以测试HTTP请求和响应
- 测试路由、中间件、认证等
- 更接近真实使用场景

**示例**:
```php
// 测试API端点
public function testCanCreateArticle()
{
    $response = $this->postJson('/api/articles/add', [
        'slug' => 'test-article',
        'translations' => [...],
    ]);
    
    $response->assertStatus(200);
    $this->assertDatabaseHas('articles', ['slug' => 'test-article']);
}

// 测试会话和重定向
public function testCanSwitchCurrency()
{
    $response = $this->post("/en/currency-switcher/update", [
        'currency_code' => 'USD',
    ]);
    
    $response->assertRedirect();
    $this->assertEquals('USD', session('currency'));
}
```

## 测试覆盖情况

### 枚举类测试（100%覆盖）
- ✅ OrderStatusEnumTest
- ✅ PaymentMethodEnumTest  
- ✅ ProductStatusEnumTest
- ✅ PromotionConditionTypeEnumTest
- ✅ PromotionDiscountTypeEnumTest
- ✅ PromotionTypeEnumTest
- ✅ ShippingMethodEnumTest

### Model类测试（约30%覆盖）
- ✅ AddressTest
- ✅ ArticleTest
- ✅ ArticleTranslationTest
- ✅ CartTest
- ✅ CartItemTest
- ✅ CategoryTest
- ✅ CountryTest
- ✅ CurrencyTest
- ✅ LanguageTest
- ✅ OrderTest
- ✅ OrderItemTest
- ✅ ProductTest
- ✅ ProductVariantTest
- ✅ PromotionTest
- ✅ PromotionRuleTest
- ✅ UserTest
- ✅ ZoneTest

### Service类测试（约50%覆盖）
- ✅ CartServiceTest
- ✅ LocaleCurrencyServiceTest
- ✅ PaymentServiceTest
- ✅ PromotionServiceTest
- ✅ ShippingServiceTest
- ✅ ShippingCalculatorFactoryTest
- ✅ SFExpressCalculatorTest
- ✅ EMSCalculatorTest

### Feature测试（已开始）
- ✅ ArticleApiTest
- ✅ LanguageCurrencySwitcherTest

## 运行测试

### 运行所有测试
```bash
php bin/phpunit
```

### 运行Unit测试
```bash
php bin/phpunit tests/Unit/
```

### 运行Feature测试
```bash
php bin/phpunit tests/Feature/
```

### 运行特定测试类
```bash
php bin/phpunit tests/Unit/CartServiceTest.php
```

### 运行特定测试方法
```bash
php bin/phpunit --filter testGetCart
```

### 运行枚举类测试
```bash
php bin/phpunit tests/Unit/ --filter Enum
```

## 测试最佳实践

1. **测试命名**: 使用描述性的测试方法名，如 `testCanCreateArticle()`
2. **AAA模式**: Arrange（准备）-> Act（执行）-> Assert（断言）
3. **单一职责**: 每个测试方法只测试一个功能点
4. **独立性**: 测试之间不应该相互依赖
5. **使用Factory**: 使用Factory创建测试数据，而不是直接操作数据库
6. **清理数据**: 使用 `RefreshDatabase` trait 确保每次测试后数据库状态干净

## 待完成的测试

### Model类（剩余约20个）
- AttributeTest
- AttributeValueTest
- ContactTest
- OrderShipmentTest
- ProductReviewTest
- SpecificationTest
- UserGroupTest
- 等等...

### Service类（剩余约8个）
- PaymentManagerTest
- PaypalGatewayTest
- TranslationServiceTest
- SearchEnginePushServiceTest
- RequestQueryCacheServiceTest
- 等等...

### Feature测试
- 购物车功能测试
- 结账流程测试
- 支付流程测试
- 用户认证测试
- 订单管理测试
- 等等...

