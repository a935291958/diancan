# 家庭点餐小程序 · ApiPost 接口文档

本目录文件可直接导入 [ApiPost](https://www.apipost.cn)。

| 文件 | 用途 | ApiPost 导入类型 |
| --- | --- | --- |
| `openapi.json` | OpenAPI 3.0.3，含完整模型与说明 | **Swagger / OpenAPI** |
| `postman.collection.json` | Postman Collection v2.1，含示例请求体 | **Postman** |
| `postman.environment.json` | 环境变量 `baseUrl` / `token` | **Postman 环境** |

## 导入步骤

1. 打开 ApiPost，进入目标项目。
2. **项目设置 → 导入**（或数据源管理 → 导入）。
3. 推荐先导入 `postman.collection.json`（类型选 **Postman**），再导入 `postman.environment.json`。
4. 也可以只导入 `openapi.json`（类型选 **Swagger**）。
5. 将环境 `baseUrl` 改为实际地址（默认 `http://127.0.0.1:9501`）。
6. 先调 **微信登录**，把返回的 `data.token` 填进环境变量 `token`。

登录成功后，其余接口请求头为：

```http
Authorization: Bearer {token}
Content-Type: application/json
```

## 约定

- 统一响应：`{"code":200,"message":"成功","data":{}}`
- `code` 业务码：200 成功；400 参数错误；401 未登录；403 家庭无权；404 不存在；409 重复提交；422 校验失败；429 限流；500 服务器错误
- 除 `/auth/wx-login`、`GET /uploads/*` 外均需 Token
- 家庭数据按当前用户所属 `family_id` 隔离
- 写接口 2 秒内防重复提交（上传接口除外）
- 点餐状态：`1` 待制作 → `2` 制作中 → `3` 已完成；`4` 已取消
- 餐段：`早` / `中` / `晚`

UniApp 现有路径（`/user/info`、`/v1/order/today` 等）与 REST 别名均已收录。
