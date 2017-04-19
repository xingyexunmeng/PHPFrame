1.

2.table事件系统支撑表
CREATE TABLE `event`(
    id int primary key auto_increment,
    cpu tinyint not null default 100 comment '最低cpu多少可以运行 百分比',
    mem tinyint not null default 100 comment '最低剩余多少内存可以运行 百分比',
    fun varchar(512) not null default '' comment 'serialize数组,0:classname 1:function name 2:parameter',
    gettime int not null default 0 comment '获取时间,5分钟内仅可试运行一次,防止重复运行'
)DEFAULT CHARSET=utf8;