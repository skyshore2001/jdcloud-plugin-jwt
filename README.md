# jwt认证

- jwt编码格式参考：http://jwt.io
 JSON Web Tokens are an open, industry standard RFC 7519 method for representing claims securely between two parties.

- 引用了php-jwt库：https://github.com/firebase/php-jwt

## 说明

典型场景是前端对应多个服务（比如微服务架构下），其中有提供登录接口的认证服务，它生成token；其余是普通服务，检查token中认证信息。

- 认证服务的后端在 login 接口中返回以jwt编码的`_token`字段，格式如`{头}.{内容}.{签名}`

- 前端登录（调用login接口）后记住该字段，并在随后的所有请求中添加HTTP头：

		Authorization: Bearer xxx.xxx.xxxxx(token内容)

- 其它服务检查jwt签名是否正确，并将其内容合并到SESSION中，筋斗云后端通过检查 SESSION 得到认证和授权（hasPerm机制）

## 用法

### 安装插件

使用git clone下载插件后，假定插件路径与jdcloud项目路径相同。进入jdcloud项目下，打开git-bash运行命令安装插件：

	./tool/jdcloud-plugin.sh add ../jdcloud-plugin-jwt

若要删除插件可以用

	./tool/jdcloud-plugin.sh del jdcloud-plugin-jwt

添加或更新的文件将自动添加到git库中，插件安装信息保存在文件plugin.dat中。

### 后端实现

login接口生成jwt token: 在login插件的扩展接口(php/class/LoginImp.php)中找到登录回调接口onLogin，添加生成token代码，示例：

	class LoginImp extends LoginImpBase
	{
		...
		function onLogin($type, $id, &$ret)
		{
			... 设置session内容 ...
			// 生成token，内容直接为SESSION
			$ret["_token"] = JWT::encode($_SESSION);

			/* 如果想只用于管理端，可加条件如
			if ($type === "emp") {
				$ret["_token"] = ...
			}
			*/
		}
	}

要使用jwt的后端服务，在conf.php中添加jwt认证方式：

	class Conf extends ConfBase
	{
		...
		static $authTypes = ["jwt"];
	}

安装插件后，jwt认证方式注册的代码（ConfBase::$authHandlers["jwt"]）添加在plugin/index.php中。

### 前端实现

首先，在登录后应自动记录token到localStorage或sessionStorage。
如果是本插件实现的后端login接口，则可直接设置：(store.js中)

	g_args.autoLogin = true;

然后，在发起调用时，取出并在HTTP头上加上该token，可以通过扩展callSvr来实现：(store.js中)

	WUI.callSvrExt["default"] = {
		beforeSend: function (opt) {
			var token = WUI.loadLoginToken();
			if (token) {
				$.extend(true, opt, { headers: {
					Authorization: "Bearer " + token
				}});
			}
		}
	}

