# 数据库压力测试(mysqlslap&sysbench)

## 使用Mysqlslap工具对磁盘进行压力测试

以下是一些常见参数名字以及相关说明

| 参数名                                    | 说明                                                         |
| :---------------------------------------- | :----------------------------------------------------------- |
| login-path=#                              | 新版本 MySQL 提供的登录方式                                  |
| -a, --auto-generate-sql                   | 自动生成 SQL 语句                                            |
| --auto-generate-sql-add-autoincrement     | 在自动生成的表中添加自增列                                   |
| --auto-generate-sql-execute-number=#      | 测试中，执行 SQL 的总次数                                    |
| --auto-generate-sql-guid-primary          | 生成基于 GUID 的主键                                         |
| --auto-generate-sql-load-type=name        | 测试的负载模型，包括 mixed, update, write, key,read，默认是 mix |
| --auto-generate-sql-secondary-indexes=#   | 自动生成的表中，二级索引的数量                               |
| --auto-generate-sql-unique-query-number=# | 测试中，使用唯一索引的查询语句数量                           |
| --auto-generate-sql-unique-write-number=# | 测试中，使用唯一索引的 DML 语句数量                          |
| --auto-generate-sql-write-number=#        | 测试中，每个线程执行的 insert 语句数量，默认为 100           |
| --commit=#                                | 测试中，每多少个语句执行一次 commit                          |
| -c, --concurrency=name                    | 测试中，并发的线程数/客户端数                                |
| --create=name                             | 自定义建表语句，或者是 SQL 文件的地址                        |
| --create-schema=name                      | 测试中，使用的数据库名                                       |
| --detach=#                                | 测试中，每执行一定数量的语句后进行重连                       |
| -e, --engine=name                         | 指定建表时的存储引擎                                         |
| -h, --host=name                           | 指定测试实例的 host 地址                                     |
| -u, --user=name                           | 指定测试实例的用户名                                         |
| -p, --password=name                       | 指定测试实例的密码                                           |
| -P, --port=#                              | 指定测试实例的端口                                           |
| -i, --iterations=#                        | 指定测试重复的次数                                           |
| --no-drop                                 | 指定测试完成后不删除测试用的库表                             |
| -x, --number-char-cols=name               | 指定测试表中 varchar 列的数量                                |
| -y, --number-int-cols=name                | 指定测试表中 int 列的数量                                    |
| --number-of-queries=#                     | 指定每个线程执行的 SQL 语句数量上限（不精确）                |
| --only-print                              | 类似于 dry run，输出会进行的操作，但是不会真的执行           |
| -F, --delimiter=name                      | 使用文件中提供的 SQL 语句时，显式指定语句之间的分隔符        |
| --post-query=name                         | 指定测试完成后，执行的查询语句，或者是 SQL 语句的文件        |
| --pre-query=name                          | 指定测试开始前，执行的查询语句，或者是 SQL 语句的文件        |
| -q, --query=name                          | 指定测试时，执行的查询语句，或者是 SQL 语句的文件            |





### 进行一次单线程测试

```
mysqlslap -a -uroot -p
```

### 并发100个连接测试

```
mysqlslap -a -c 100 -uroot -p
```

### 执行一次测试，分别100和200个并发，执行5000次总查询

```
mysqlslap -a --concurrency=100,200 --number-of-queries 5000 --iterations=5 -uroot -p
```

### 测试200个并发线程，测试次数1次，自动生成SQL测试脚本，读、写、更新混合测试，自增长字段，测试引擎为innodb，共运行5000次查询

```
mysqlslap -uroot -p --concurrency=200 --iterations=1 --auto-generate-sql --auto-generate-sql-load-type=mixed --auto-generate-sql-add-autoincrement --engine=innodb --number-of-queries=5000
```

### 测试同时不同的存储引擎的性能进行对比

```
mysqlslap -uroot -p -a --concurrency = 50,100 --number-of-queries 1000 --iterations=5 --engine=myisam,innodb
```

但是这一条具体执行的时候非常的奇怪 打印了1000多条 





## 使用sysbench进行mysql压力测试

### 安装sysbench

#### 1.下载安装包

```
wget https://github.com/akopytov/sysbench/archive/1.0.zip -O "sysbench-1.0.zip"
unzip sysbench-1.0.zip
cd sysbench-1.0
```

#### 2.安装依赖库

```
sudo apt-get install automake libtool -y
```

#### 3.开始安装

```
./autogen.sh
./configure
./configure --with-mysql-includes=/app/teledb/mysql/include/ --with-mysql-libs=/app/teledb/mysql/lib/
make
make install
```

在实际安装中并没有make，相应的解决方案如下所示

[(3条消息) make 命令出现："make:*** No targets specified and no makefile found.Stop."_shun35的博客-CSDN博客](https://blog.csdn.net/shun35/article/details/94576800)

然后安装成功之后测试一下sysbench --version 之后就可以开始正常使用了

### 使用sysbench对数据库进行压力测试

#### 1.CPU测试

```
cat /proc/cpuinfo //查看cpu参数
sysbench --test=cpu --cpu-max-prime=20000 run
```

#### 2.I/O基准测试

- seqwr 顺序写入
- seqrewr 顺序重写
- seqrd 顺序读取
- rndrd 随机读取
- rndwr 随机写入
- rndrw 混合随机读/写

##### 创建16个文件

```
sysbench --test=fileio --file-num=16 --file-total-size=1G prepare
```

##### 查看已经有的文件

```
ll -trhl
```

##### 开始fileio测试(使用16个线程随机读取)

```
sysbench --test=fileio --file-total-size=1G --file-test-mode=rndrd --max-time=180 --max-requests=100000000 --num-threads=16 --file-num=16 --file-extra-flags=direct --file-fsync-freq=0 --file-block-size=16384 run
```

##### 这个是原来的 但是需要更改一下

```
sysbench --test=fileio --file-total-size=1G --file-test-mode=rndrd --time=180 --events=100000000 --threads=16 --file-num=16 --file-extra-flags=direct --file-fsync-freq=0 --file-block-size=16384 run
```

##### 清除相对应的文件

```
sysbench --test=fileio --file-total-size=1G cleanup
```

