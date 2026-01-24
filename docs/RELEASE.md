# 发布新版本指南

本文档说明如何在 GitHub 和 Gitee 上发布新版本。

## 📋 发布前准备

### 1. 确保代码已提交

```bash
# 检查当前状态
git status

# 提交所有更改
git add .
git commit -m "准备发布 v1.0.0"

# 推送到远程仓库
git push origin main
```

### 2. 更新版本号

在发布新版本前，需要更新以下文件中的版本信息：

- `README.md` - 更新版本说明（如果需要）
- `CHANGELOG.md` - 添加版本更新日志（如果存在）

## 🏷️ 创建 Git Tag

### 方法一：使用命令行创建 Tag

```bash
# 1. 创建带注释的 tag（推荐）
git tag -a v1.0.0 -m "发布 v1.0.0 - 首个正式版本"

# 或者创建轻量级 tag
git tag v1.0.0

# 2. 查看创建的 tag
git tag -l

# 3. 查看 tag 详情
git show v1.0.0

# 4. 推送 tag 到 GitHub
git push origin v1.0.0

# 5. 推送 tag 到 Gitee
git push gitee v1.0.0

# 或者一次性推送所有 tag
git push origin --tags
git push gitee --tags
```

### 方法二：在 GitHub/Gitee 网页上创建

1. 访问仓库的 Releases 页面
2. 点击 "Create a new release" 或 "发布新版本"
3. 填写版本信息（会自动创建 tag）

## 🚀 GitHub 发布步骤

### 1. 通过网页界面发布（推荐）

1. **访问 Releases 页面**
   - 打开仓库：`https://github.com/TeanaryService/teanary_srvice`
   - 点击右侧 "Releases" 链接
   - 或直接访问：`https://github.com/TeanaryService/teanary_srvice/releases`

2. **创建新 Release**
   - 点击 "Create a new release" 按钮
   - 或点击 "Draft a new release"

3. **填写版本信息**
   ```
   Tag version: v1.0.0
   Release title: v1.0.0 - 首个正式版本
   Description: 
   ## 🎉 首个正式版本发布！
   
   ### ✨ 新增功能
   - 多节点数据双向同步系统
   - AI自动翻译功能（支持8种语言）
   - Chrome插件商品采集工具
   - 完整的电商功能模块
   - 现代化Livewire管理后台
   - Laravel Octane高性能部署
   
   ### 📦 下载
   - [ZIP 压缩包](https://github.com/TeanaryService/teanary_srvice/archive/refs/tags/v1.0.0.zip)
   - [TAR.GZ 压缩包](https://github.com/TeanaryService/teanary_srvice/archive/refs/tags/v1.0.0.tar.gz)
   
   ### 📚 文档
   - [安装指南](../README.md#快速开始)
   - [多节点同步文档](SYNC.md)
   ```

4. **上传发布文件（可选）**
   - 可以上传编译后的文件、安装包等
   - 点击 "Attach binaries" 上传文件

5. **发布**
   - 选择 "Set as the latest release"（设置为最新版本）
   - 点击 "Publish release" 发布

### 2. 使用 GitHub CLI 发布

```bash
# 安装 GitHub CLI（如果未安装）
# macOS: brew install gh
# Linux: 参考 https://cli.github.com/manual/installation

# 登录 GitHub
gh auth login

# 创建 Release
gh release create v1.0.0 \
  --title "v1.0.0 - 首个正式版本" \
  --notes "## 🎉 首个正式版本发布！

### ✨ 新增功能
- 多节点数据双向同步系统
- AI自动翻译功能（支持8种语言）
- Chrome插件商品采集工具
- 完整的电商功能模块
- Laravel Octane高性能部署"

# 或者从文件读取说明
gh release create v1.0.0 \
  --title "v1.0.0 - 首个正式版本" \
  --notes-file RELEASE_NOTES.md
```

## 🚀 Gitee 发布步骤

### 1. 通过网页界面发布

1. **访问仓库**
   - 打开仓库：`https://gitee.com/teanary/teanary_service`
   - 点击 "发行版" 标签
   - 或直接访问：`https://gitee.com/teanary/teanary_service/releases`

2. **创建新发行版**
   - 点击 "创建发行版" 或 "发布新版本" 按钮

3. **填写版本信息**
   ```
   标签版本: v1.0.0
   发行版标题: v1.0.0 - 首个正式版本
   发行说明: 
   ## 🎉 首个正式版本发布！
   
   ### ✨ 新增功能
   - 多节点数据双向同步系统
   - AI自动翻译功能（支持8种语言）
   - Chrome插件商品采集工具
   - 完整的电商功能模块
   - 现代化Livewire管理后台
   - Laravel Octane高性能部署
   
   ### 📦 下载
   - [ZIP 压缩包](https://gitee.com/teanary/teanary_service/repository/archive/v1.0.0.zip)
   - [TAR.GZ 压缩包](https://gitee.com/teanary/teanary_service/repository/archive/v1.0.0.tar.gz)
   ```

4. **上传附件（可选）**
   - 可以上传编译后的文件、安装包等

5. **发布**
   - 点击 "发布" 按钮

### 2. 使用命令行发布

Gitee 没有官方的 CLI 工具，但可以通过 API 发布：

```bash
# 需要先获取 Gitee Access Token
# 访问：https://gitee.com/profile/personal_access_tokens

# 使用 curl 创建 Release
curl -X POST \
  -H "Content-Type: application/json" \
  -H "Authorization: token YOUR_GITEE_TOKEN" \
  -d '{
    "tag_name": "v1.0.0",
    "name": "v1.0.0 - 首个正式版本",
    "body": "## 🎉 首个正式版本发布！\n\n### ✨ 新增功能\n- 多节点数据双向同步系统\n- AI自动翻译功能（支持8种语言）\n- Chrome插件商品采集工具"
  }' \
  https://gitee.com/api/v5/repos/teanary/teanary_service/releases
```

## 📝 版本号规范

建议使用 [语义化版本](https://semver.org/lang/zh-CN/) 规范：

- **主版本号（MAJOR）**：不兼容的 API 修改
- **次版本号（MINOR）**：向下兼容的功能性新增
- **修订号（PATCH）**：向下兼容的问题修正

示例：
- `v1.0.0` - 首个正式版本
- `v1.1.0` - 新增功能
- `v1.1.1` - 修复 bug
- `v2.0.0` - 重大更新，可能不兼容

## 🔄 完整发布流程示例

```bash
# 1. 确保所有更改已提交
git add .
git commit -m "准备发布 v1.0.0"
git push origin main
git push gitee main

# 2. 创建并推送 tag
git tag -a v1.0.0 -m "发布 v1.0.0 - 首个正式版本"
git push origin v1.0.0
git push gitee v1.0.0

# 3. 在 GitHub 创建 Release（使用网页界面或 CLI）
# 4. 在 Gitee 创建发行版（使用网页界面）
```

## 📋 Release Notes 模板

```markdown
## 🎉 v1.0.0 - 首个正式版本

**发布日期**: 2026-01-11

### ✨ 新增功能

- 🌍 多节点数据双向同步系统
  - 支持任意数量节点间的数据同步
  - 自动重试和故障恢复
  - 完整的同步监控

- 🤖 AI自动翻译功能
  - 支持 8 种语言自动翻译
  - 集成 Ollama 本地 AI
  - 批量翻译处理

- 🛒 Chrome插件商品采集
  - 1688 商品一键采集
  - 图片自动下载上传
  - 批量商品导入

- 🛍️ 完整电商功能
  - 多规格商品管理
  - 购物车和订单系统
  - 支付集成（PayPal等）

- 🎨 现代化管理后台
  - Livewire Manager 管理面板
  - 实时数据统计
  - 多语言内容管理

- ⚡ 高性能架构
  - Laravel Octane 高性能服务器
  - Redis 缓存加速
  - CDN 静态资源加速

### 🔧 技术栈

- Laravel 12.x
- PHP 8.1+
- MySQL 8.0+
- Redis
- Tailwind CSS 3.x
- Livewire 3.x

### 📦 安装

```bash
git clone https://github.com/TeanaryService/teanary_srvice.git
cd teanary_service
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
```

### 📚 文档

- [快速开始](../README.md#快速开始)
- [多节点同步文档](SYNC.md)
- [部署指南](../README.md#部署指南)

### 🔗 下载

- [ZIP 压缩包](https://github.com/TeanaryService/teanary_srvice/archive/refs/tags/v1.0.0.zip)
- [TAR.GZ 压缩包](https://github.com/TeanaryService/teanary_srvice/archive/refs/tags/v1.0.0.tar.gz)

### 🙏 致谢

感谢所有贡献者和用户的支持！
```

## ⚠️ 注意事项

1. **Tag 命名规范**
   - 使用 `v` 前缀：`v1.0.0`
   - 保持版本号格式一致
   - 不要使用已存在的 tag 名称

2. **Release Notes**
   - 使用 Markdown 格式
   - 清晰列出新功能和改进
   - 包含下载链接
   - 添加安装说明

3. **同步两个平台**
   - 确保 GitHub 和 Gitee 的版本信息一致
   - 两个平台的 tag 名称应该相同
   - Release Notes 可以略有不同（适应不同平台）

4. **更新文档**
   - 更新主 `README.md` 中的版本说明（如果需要）

## 🔗 相关链接

- [GitHub Releases 文档](https://docs.github.com/en/repositories/releasing-projects-on-github)
- [Gitee 发行版文档](https://gitee.com/help/articles/4129)
- [语义化版本规范](https://semver.org/lang/zh-CN/)
