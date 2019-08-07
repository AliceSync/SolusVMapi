<?php
$systempwd = '';//password
$key = '';//you key
$hash = '';//you hash
$title = 'SolusVMapi控制程序';
$apiurl = 'http://svm.raksmart.com:5353/api/client/command.php';
session_start();
@$_GET['p'] = $_GET['p'];
@$_SESSION['u'] = $_SESSION['u'];
if($_GET['p'] != 'log_post' and $_GET['p'] != 'post_reboot'){
    echo '<!DOCTYPE html>
    <html lang="zh-cn">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>'.$GLOBALS['title'].'</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="https://cdn.staticfile.org/amazeui/2.7.2/css/amazeui.css">
        <link rel="stylesheet" href="https://cdn.staticfile.org/amazeui/2.7.2/css/amazeui.min.css">
        <script src="http://lib.zslm.org/js/jquery/jquery-3.1.1.min.js"></script>
        <script src="https://cdn.staticfile.org/amazeui/2.7.2/js/amazeui.js"></script>
        <script src="https://cdn.staticfile.org/amazeui/2.7.2/js/amazeui.min.js"></script>
        <script src="https://cdn.staticfile.org/amazeui/2.7.2/js/amazeui.ie8polyfill.js"></script>
        <script src="https://cdn.staticfile.org/amazeui/2.7.2/js/amazeui.ie8polyfill.min.js"></script>
        <script src="https://cdn.staticfile.org/amazeui/2.7.2/js/amazeui.widgets.helper.js"></script>
        <script src="https://cdn.staticfile.org/amazeui/2.7.2/js/amazeui.widgets.helper.min.js"></script>
    </head>
    <body>';
}
        switch ($_GET['p']) {
            case 'log':
                if($_SESSION['u'] == 'login'){
                    header('location: ?p=index');
                    exit_('<p>已经是登录状态</p>');
                    break;
                }
                exit_('<center><h1 class="am-sm-only-text-center">'.$GLOBALS['title'].'</h1></center><div class="am-form"><div class="am-form-group">
                <input type="password" id="pwd" placeholder="键入登录密码" onkeydown="gonext()">
              </div><button type="button" id="login_btn" class="am-btn am-btn-primary am-btn-block am-btn-xl">登录</button></div><script>
              $("#login_btn").click(function(){
                  $.ajax({
                      type: "post",
                      url: "?p=log_post",
                      data: "pwd="+$("#pwd").val(),
                      async:true,
                      dataType:"json",
                      success: function (res) {
                          if(res.code == 1 && res.msg == "ok"){
                              msg_("成功,等待跳转");
                              window.location = "?p=index";
                          }else{
                              msg_("失败,"+res.msg);
                          }
                      },
                      error:function(){
                          msg_("网络或服务器错误");
                      }
                  });
              })
          </script>');
                break;

            case 'log_post':
                if($_SESSION['u'] == 'login'){
                    exit_(json_encode(['code'=>0,'msg'=>'已经是登录状态']));
                    break;
                }
                if($_POST['pwd'] == $GLOBALS['systempwd']){
                    $_SESSION['u'] = 'login';
                    exit_(json_encode(['code'=>1,'msg'=>'ok']));
                }else{
                    exit_(json_encode(['code'=>0,'msg'=>'你猜密码是不是错了?']));
                }
                
                break;
            
            case 'out':
                if($_SESSION['u'] != 'login'){
                    header('location: ?p=log');
                    exit_('<p>你没登录要退出啥。。。</p>');
                    break;
                }
                $_SESSION['u'] = '';
                header('location: ?p=log');
                exit_('<p>退出完成,需要重新登录刷新下就行</p>');
                break;

            case 'post_reboot':
                if($_SESSION['u'] != 'login'){
                    exit_(json_encode(['code'=>0,'msg'=>'没有登录不要请求这里啦']));
                    break;
                }
                $key = $GLOBALS['key'];
                $hash = $GLOBALS['hash'];
                $url = "$apiurl?key=$key&hash=$hash&action=reboot";//boot 启动 reboot 重启 status 状态 info 详情
                @$data = file_get_contents($url);
                preg_match_all('/<(.*?)>([^<]+)<\/\\1>/i', $data, $match);
                $result = array();
                foreach ($match[1] as $x => $y) {
                    $result[$y] = $match[2][$x];
                }
                if($result['status'] == 'success' and $result['statusmsg'] == 'rebooted'){
                    exit_(json_encode(['code'=>1,'msg'=>'ok']));
                }else{
                    exit_(json_encode(['code'=>0,'msg'=>'重启命令好像没有成功将冒号后的信息提交issuse::'.$data]));
                }
                break;
            case 'post_ping':
                if($_SESSION['u'] != 'login'){
                    exit_(json_encode(['code'=>0,'msg'=>'没有登录不要请求这里啦']));
                    break;
                }
                exit_(json_encode(['code'=>1,'msg'=>'接口还没有准备好哦']));
                break;
            default:
                if($_SESSION['u'] != 'login'){
                    header('location: ?p=log');
                    exit_('<p>尚未登陆_请等待跳转后再试</p>');
                    break;
                }
                $key = $GLOBALS['key'];
                $hash = $GLOBALS['hash'];
                $url = "$apiurl?key=$key&hash=$hash&action=status";//boot 启动 reboot 重启 status 状态 info 详情
                @$data = file_get_contents($url);
                preg_match_all('/<(.*?)>([^<]+)<\/\\1>/i', $data, $match);
                $result = array();
                foreach ($match[1] as $x => $y) {
                    $result[$y] = $match[2][$x];
                }
                exit_('<ul class="am-nav"><li class="am-active"><a href="::JavaScript">'.$GLOBALS['title'].'</a></li></ul><div class="am-panel am-panel-primary"><header class="am-panel-hd">
                <h3 class="am-panel-title">服务器信息</h3>
              </header>
              <div class="am-panel-bd">
              <p>访问时间:'.date('Y年m月d日H时i分s秒').'</p>
              <p>获取状态:'.(($result['status'] == 'success')?'<ll class="am-text-success">成功':'<ll class="am-text-danger">失败，将冒号后的信息提交issuse:'.$data.'</ll>').'</p>
              '.(($result['status'] == 'success')?'<p>服务器状态信息:'.(($result['statusmsg'] == 'online')?'<ll class="am-text-success">在线</ll>':'<ll class="am-text-danger">离线</ll>').'</p>
              <p>虚拟服务器状态:'.(($result['vmstat'] == 'online')?'<ll class="am-text-success">在线</ll>':'<ll class="am-text-danger">离线</ll>').'</p>
              <p>服务器名称:'.$result['hostname'].'</p>
              <p>服务器IP:'.$result['ipaddress'].'</p>':'<p class="am-text-danger">获取失败时其他信息无法显示</p>').'
              <p>操作:
              <a href="?p=index" class="am-btn am-btn-primary am-btn-block am-btn-xl">刷新信息</a>
              <button type="button" id="reboot_btn" class="am-btn am-btn-success  am-btn-block am-btn-xl">重启一下</button>
              <button type="button" id="ping_btn" class="am-btn am-btn-error  am-btn-block am-btn-xl">PING</button>
              <a href="?p=out" class="am-btn am-btn-danger  am-btn-block am-btn-xl">退出登录</a></p>
              </div></div><script>
              $("#reboot_btn").click(function(){
                  $.ajax({
                      type: "get",
                      url: "?p=post_reboot",
                      async:true,
                      dataType:"json",
                      success: function (res) {
                          if(res.code == 1 && res.msg == "ok"){
                              msg_("操作成功.查看最新状态请等待几秒后自行刷新");
                          }else{
                              msg_("失败,"+res.msg);
                          }
                      },
                      error:function(){
                          msg_("网络或服务器错误");
                      }
                  });
                })
            $("#ping_btn").click(function(){
                $.ajax({
                    type: "get",
                    url: "?p=post_ping",
                    async:true,
                    dataType:"json",
                    success: function (res) {
                        if(res.code == 1 && res.msg == "ok"){
                            msg_("获得成功,状态为:"+res.msg);
                        }else{
                            msg_("失败,"+res.msg);
                        }
                    },
                    error:function(){
                        msg_("网络或服务器错误");
                    }
                });
            })
          </script>');
                break;
        }
        function exit_($str){
            echo $str;
        }
if($_GET['p'] != 'log_post' and $_GET['p'] != 'post_reboot'){
    echo '<div class="am-modal am-modal-alert" tabindex="-1" id="my-alert">
    <div class="am-modal-dialog">
        <div class="am-modal-hd">zslm.org</div>
        <div class="am-modal-bd" id="msg__"></div>
        <div class="am-modal-footer">
            <span class="am-modal-btn">确定</span>
        </div>
    </div>
</div>
<script>
    function msg_(msg=""){
        $("#my-alert").modal()
        $("#msg__").html(msg);
    }
    function gonext(evt){
        evt = (evt) ? evt : window.event;
        if(evt.keyCode == 13){
            $("#login_btn").click();
        }
    }
</script>
</body>
</html>';
}
