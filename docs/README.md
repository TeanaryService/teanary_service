# Teanary 文档中心

Teanary 项目文档索引与导航。当前以 **v1.4** 为主，兼顾稳定版 v1.3.x 的部署与运维说明。

---

## 如何查看本文档

- **在 Cursor / VS Code 里**：打开任意 `.md` 文件，按 `Ctrl+Shift+V`（或右侧「打开预览」）即可预览。
- **在浏览器里**：在项目根目录执行 `npx docsify-cli serve docs`，然后在浏览器打开提示的地址（一般为 `http://localhost:3000`）。无需构建，修改文档后刷新即可。

---

## 文档结构

### 入门与概览

| 文档 | 说明 |
|------|------|
| [项目首页](../README.md) | 根目录 README：Teanary 1.4 定位、版本状态、设计原则 |
| [README.en.md](README.en.md) | 英文项目概览与文档链接 |

### 架构与设计

| 文档 | 说明 |
|------|------|
| [architecture.md](architecture.md) | 系统架构与数据流程图：整体架构、订单/同步/商品/支付/促销等流程 |
| [vision.md](vision.md) | Teanary Vision（英文）：AI-Native 电商基础设施愿景与能力边界 |

### 部署与运维

| 文档 | 说明 |
|------|------|
| [deployment.md](deployment.md) | 部署指南：开发环境、生产环境（Deployer）、Nginx/Supervisor、常见问题 |

### 多节点与同步

| 文档 | 说明 |
|------|------|
| [sync.md](sync.md) | 数据双向同步方案：多节点配置、SyncService、文件同步、故障排查与扩展 |

### 社区与共建

| 文档 | 说明 |
|------|------|
| [cobuilding.md](cobuilding.md) | 共建招募：面向外贸从业者的参与方式与开源协议说明 |

---

## 按角色导航

- **新手 / 评估**：根目录 [README](../README.md) → [deployment.md](deployment.md) 本地启动 → [architecture.md](architecture.md) 了解架构  
- **开发**：[architecture.md](architecture.md) → [sync.md](sync.md) → [vision.md](vision.md)  
- **运维 / SRE**：[deployment.md](deployment.md) → [sync.md](sync.md)（按需启用多节点）

---

## 历史与归档

- 历史发布说明、旧版文档、脚本示例等见 **[archive/](archive/)**。  
- 文档与代码不一致时，欢迎提 Issue 或 Pull Request。

---

*最后更新：2026-03*
