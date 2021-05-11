<?php
error_reporting(0);

class User
{
    public $sql;

    public function __construct()
    {
        $this->sql = new mysqli("127.0.0.1", "user", "user", "imgbed");
        if (mysqli_connect_errno()) {#检查连接
            printf("Connect failed: %s\n", mysqli_connect_error());
            exit();
        }
        #var_dump($this->sql);
    }

    public function check_user_exist($username): bool
    {
        $stmt = $this->sql->prepare("SELECT `username` FROM `users` WHERE `username` = ?;");
        #https://www.php.net/manual/zh/mysqli.prepare.php
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        $count = $stmt->affected_rows;#返回匹配的总行数
        $stmt->close();
        if ($count === 0) {
            return false;
        }
        return true;
    }

    public function add_user($username, $password): bool
    {
        if ($this->check_user_exist($username)) {
            return false;
        }
        $password = sha1($password . "!@#$%^&*()");#混淆
        $stmt = $this->sql->prepare("INSERT INTO `users` (`id`, `username`, `password`) VALUES (NULL, ?, ?);");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $stmt->close();
        return true;
    }

    public function verify_user($username, $password): bool
    {
        if (!$this->check_user_exist($username)) {
            return false;
        }
        $password = sha1($password . "!@#$%^&*()");#混淆
        $stmt = $this->sql->prepare("SELECT `password` FROM `users` WHERE `username` = ?;");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->bind_result($result);
        $stmt->fetch();
        $stmt->close();
        if (isset($result) && $result === $password) {
            return true;
        }
        return false;
    }

    public function __destruct()
    {
        $this->sql->close();
    }
}

class FileList
{
    private $filename;
    private $size;

    public function __construct($path)
    {
        $this->filename = array();
        $this->size = array();
        $files = scandir($path);

        $key = array_search(".", $files);#剔除.和..以及index.html
        unset($files[$key]);
        $key = array_search("..", $files);
        unset($files[$key]);
        $key = array_search("index.html", $files);
        unset($files[$key]);
        foreach ($files as $value) {
            $file = new File($path . $value);
            if ($file->check_file_exist()) {
                array_push($this->filename, $file->get_file_name());
                array_push($this->size, $file->get_file_size());
            }
        }
    }

    public function __destruct()
    {
        $table = '<div id="container" class="container"><div class="table-responsive"><table id="table" class="table table-bordered table-hover sm-font">';#https://getbootstrap.com/docs/4.0/content/tables/
        $table .= '<thead><th scope="col" class="text-center">文件名</th><th scope="col" class="text-center">大小</th><th scope="col" class="text-center">操作</th></thead>';
        $table .= '<tbody>';
        for ($i = 0; $i < count($this->filename); $i++) {
            $url = 'http://0.0.0.0/' . $_SESSION['sandbox'] . $this->filename[$i];
            $table .= '<tr>';
            $table .= '<td class="text-center">' . htmlentities($url) . '</td>';
            $table .= '<td class="text-center">' . htmlentities($this->size[$i]) . '</td>';
            $table .= '<td class="text-center" filename="' . htmlentities($this->filename[$i]) . '"><a href="#" class="download">下载</a> / <a href="' . htmlentities($url) . '" target="_blank" class="preview">预览</a> / <a href="#" class="delete">删除</a></td>';
            $table .= '</tr>';
        }
        $table .= '</tbody></table></div>';
        echo $table;
    }
}

class File
{
    public $filename;

    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    public function check_file_exist(): bool
    {
        if (file_exists($this->filename) && !is_dir($this->filename)) {
            return true;
        } else {
            return false;
        }
    }

    public function get_file_name()
    {
        return basename($this->filename);
    }

    public function get_file_size(): string
    {
        $size = filesize($this->filename);
        $units = array('B', 'KB', 'MB', 'GB');
        for ($i = 0; $size >= 1024; $i++) {
            $size /= 1024;
        }
        return round($size, 1) . ' ' . $units[$i];#浮点数四舍五入,保留1位小数
    }

    public function delete_file()
    {
        unlink($this->filename);
    }

    public function get_file_contents()
    {
        return file_get_contents($this->filename);
    }
}

