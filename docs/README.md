# Teanary 项目文档

欢迎查阅 Teanary 项目文档。本文档目录提供了所有可用文档的索引。

## 📚 文档列表

### 核心功能文档

#### 系统架构
- **[系统架构与数据流程图](ARCHITECTURE.md)** - 系统整体架构与数据流程图
  - 系统整体架构图
  - 订单处理流程
  - 多节点数据同步流程
  - 商品创建流程
  - 支付处理流程
  - 促销计算流程
  - 核心组件说明

#### 部署与运维
- **[部署指南](DEPLOYMENT.md)** - 快速部署指南
  - 开发环境：3 步快速启动
  - 生产环境：使用 Deployer 一键部署
  - 环境要求和服务器配置
  - 常见问题排查

#### 多节点同步
- **[多节点数据同步](SYNC.md)** - 详细的多节点数据双向同步配置和使用指南
  - 快速开始
  - 工作原理
  - 配置说明
  - 文件同步功能
  - 故障排查
  - 同步测试

#### 功能文档
- **[流量统计功能](TRAFFIC-STATISTICS.md)** - 流量统计功能完整文档
  - 功能概述和特性
  - 技术架构
  - 配置说明
  - 使用方法
  - 数据管理
  - 常见问题

### 开发文档

#### 代码架构
- **[批量操作实现](BATCH_ACTIONS_IMPLEMENTATION.md)** - 批量操作功能实现文档
  - 功能概述
  - 技术实现
  - 使用方法
  - 扩展指南

#### 测试文档
- **[测试报告](TEST_REPORT.md)** - 单元测试和功能测试报告

### 发布文档

- **[发布新版本指南](RELEASE.md)** - 版本发布流程
  - 发布前准备
  - 创建 Git Tag
  - GitHub/Gitee 发布步骤
  - 版本号规范
  - Release Notes 模板

## 🚀 快速导航

### 新手入门

1. 查看主 [README.md](../README.md) 了解项目概况
2. 按照 [部署指南](DEPLOYMENT.md) 快速启动开发环境
3. 阅读 [系统架构与数据流程图](ARCHITECTURE.md) 了解系统架构
4. 阅读 [多节点数据同步](SYNC.md) 了解核心功能

### 开发者

1. 阅读 [系统架构与数据流程图](ARCHITECTURE.md) 了解系统整体架构
2. 阅读 [批量操作实现](BATCH_ACTIONS_IMPLEMENTATION.md) 了解批量操作功能
3. 查看 [多节点数据同步](SYNC.md) 了解同步机制
4. 参考 [发布新版本指南](RELEASE.md) 进行版本发布
5. 查看 [测试报告](TEST_REPORT.md) 了解测试覆盖情况

### 运维人员

1. 阅读 [部署指南](DEPLOYMENT.md) 使用 Deployer 一键部署
2. 阅读 [多节点数据同步](SYNC.md) 了解多节点配置
3. 查看 [流量统计功能](TRAFFIC-STATISTICS.md) 了解数据管理

## 📖 文档结构说明

### 核心功能文档

这些文档详细说明了系统的核心功能和使用方法：

- **ARCHITECTURE.md** - 系统架构设计，包括数据流程图和组件说明
- **DEPLOYMENT.md** - 完整的部署指南，从开发环境到生产环境
- **SYNC.md** - 多节点数据同步的完整文档，包括配置、使用和故障排查
- **TRAFFIC-STATISTICS.md** - 流量统计功能的详细说明

### 开发文档

这些文档面向开发者，说明如何扩展和维护系统：

- **BATCH_ACTIONS_IMPLEMENTATION.md** - 批量操作功能的实现细节
- **TEST_REPORT.md** - 测试覆盖情况和测试报告

### 发布文档

这些文档说明如何发布新版本：

- **RELEASE.md** - 版本发布流程和规范

## 🔍 查找文档

### 按主题查找

- **部署相关** → [DEPLOYMENT.md](DEPLOYMENT.md)
- **同步相关** → [SYNC.md](SYNC.md)
- **架构相关** → [ARCHITECTURE.md](ARCHITECTURE.md)
- **功能相关** → [TRAFFIC-STATISTICS.md](TRAFFIC-STATISTICS.md)
- **开发相关** → [BATCH_ACTIONS_IMPLEMENTATION.md](BATCH_ACTIONS_IMPLEMENTATION.md)
- **发布相关** → [RELEASE.md](RELEASE.md)

### 按角色查找

- **新手** → 从 [README.md](../README.md) 开始，然后阅读 [DEPLOYMENT.md](DEPLOYMENT.md)
- **开发者** → 阅读 [ARCHITECTURE.md](ARCHITECTURE.md) 和 [BATCH_ACTIONS_IMPLEMENTATION.md](BATCH_ACTIONS_IMPLEMENTATION.md)
- **运维** → 阅读 [DEPLOYMENT.md](DEPLOYMENT.md) 和 [SYNC.md](SYNC.md)

## 📝 文档更新

文档会随着项目更新而持续更新。如果发现文档有误或需要补充，欢迎提交 Issue 或 Pull Request。

## 🔗 相关链接

- [项目主页](../README.md)
- [GitHub 仓库](https://github.com/TeanaryService/teanary_srvice)
- [Gitee 仓库](https://gitee.com/teanary/teanary_service)

---

**最后更新**: 2026-01-24
