1.

2.table�¼�ϵͳ֧�ű�
CREATE TABLE `event`(
    id int primary key auto_increment,
    cpu tinyint not null default 100 comment '���cpu���ٿ������� �ٷֱ�',
    mem tinyint not null default 100 comment '���ʣ������ڴ�������� �ٷֱ�',
    fun varchar(512) not null default '' comment 'serialize����,0:classname 1:function name 2:parameter',
    gettime int not null default 0 comment '��ȡʱ��,5�����ڽ���������һ��,��ֹ�ظ�����'
)DEFAULT CHARSET=utf8;