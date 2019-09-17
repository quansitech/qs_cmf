<?php
namespace Common\Job;
class TestJob
{
    public function perform()
    {
        echo 'test_job Finished!';

//        $dbh = new \PDO('mysql:host=127.0.0.1;dbname=nxf', 'root', 'bb85b6df06');    
//        $dbh->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);    
//        $dbh->exec('set names utf8');   
//        /*添加*/  
//        //$sql = "INSERT INTO `user` SET `login`=:login AND `password`=:password";   
//        $sql = "INSERT INTO `qs_test` (`args` ,`create_date`)VALUES (:args, :create_date)";  
//        $stmt = $dbh->prepare($sql);  
//        $stmt->execute(array(':args'=>$data['args'],':create_date'=>$data['create_date']));    
//        fwrite(STDOUT, 'test_job Finished! id=' . $dbh->lastinsertid() . PHP_EOL);
    }
}

