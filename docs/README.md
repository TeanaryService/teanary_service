# Teanary 文档索引

欢迎查阅 Teanary 项目文档。这里主要聚焦 **v1.4** 相关的架构、部署与多节点同步方案。

---

## 核心文档

- **系统架构与数据流程**：`ARCHITECTURE.md`  
  - 整体架构图、核心组件说明  
  - 订单处理 / 多节点同步 / 商品创建 / 支付 / 促销等数据流程  

- **部署指南**：`DEPLOYMENT.md`  
  - 开发环境与生产环境部署步骤  
  - Deployer 自动化部署示例  
  - Nginx / Supervisor 等服务器配置示例  

- **数据双向同步方案**：`SYNC.md`  
  - 多节点双向同步整体设计  
  - 环境变量与节点配置  
  - 文件同步、重试机制与故障排查  

---

## AI 愿景与设计方向

- **Teanary Vision（英文）**：`VISION.md`  
  - AI‑Native 电商基础设施的愿景  
  - 能力边界、安全与可审计性原则  

- **Teanary 1.4 概览**  
  - 中文：根目录 `README.md`  
  - English：`READMEEN.md`  

---

## 按角色导航

### 新手 / 评估者

1. 先阅读根目录 `README.md`，了解 Teanary 1.4 的定位与核心方向  
2. 按照 `DEPLOYMENT.md` 启动本地开发环境或部署测试环境  
3. 需要了解整体架构时，阅读 `ARCHITECTURE.md`  

### 开发者

1. 详细阅读 `ARCHITECTURE.md`，理解分层架构与数据流向  
2. 阅读 `SYNC.md`，掌握多节点同步机制与扩展方式  
3. 结合 `VISION.md` 理解 AI‑Native 能力的设计边界与使用方式  

### 运维 / SRE

1. 重点阅读 `DEPLOYMENT.md`，使用其中的 Deployer、Nginx、Supervisor 示例进行部署  
2. 根据业务需要评估是否启用多节点同步（参见 `SYNC.md`）  

---

## 文档维护说明

- 本目录仅列出 **当前仍在使用的文档**。  
- 历史版本相关的发布说明、测试报告、流量统计等文档已从本分支移除，如需请在 `v1.3.x` 分支中查看。  
- 如果你发现文档与实际代码不一致，欢迎提交 Issue 或 Pull Request。  

---

**最后更新**：2026-01-27

