<?php

require(__DIR__ . "/../../vendor/autoload.php");
require(__DIR__ . "/ExampleConfig.php");

use Aliyun\OTS\OTSClient as OTSClient;

$otsClient = new OTSClient(array(
    'EndPoint' => EXAMPLE_END_POINT,
    'AccessKeyID' => EXAMPLE_ACCESS_KEY_ID,
    'AccessKeySecret' => EXAMPLE_ACCESS_KEY_SECRET,
    'InstanceName' => EXAMPLE_INSTANCE_NAME,
));

foreach ($otsClient->listTable(array()) as $tableName) {
    $otsClient->deleteTable(array('table_name' => $tableName));
}

$request = array(
    'table_meta' => array(
        'table_name' => 'MyTable',       // 表名为 MyTable
        'primary_key_schema' => array(
            'PK0' => 'INTEGER',          // 第一个主键列（又叫分片键）名称为PK0, 类型为 INTEGER
            'PK1' => 'STRING',           // 第二个主键列名称为PK1, 类型为STRING
        ),
    ),
    'reserved_throughput' => array(
        'capacity_unit' => array(
            'read' => 5,                 // 预留读写吞吐量设置为：5个读CU，和10个写CU
            'write' => 10,
        ),
    ),
);
$otsClient->createTable($request);
sleep(10);

$request = array(
    'table_name' => 'MyTable', 'condition' => 'IGNORE',
    'primary_key' => array('PK0' => 1, 'PK1' => 'Zhejiang'),
    'attribute_columns' => array('attr1' => 'Hangzhou'),
);
$response = $otsClient->putRow($request);

$request = array(
    'table_name' => 'MyTable', 'condition' => 'IGNORE',
    'primary_key' => array('PK0' => 2, 'PK1' => 'Jiangsu'),
    'attribute_columns' => array('attr1' => 'Nanjing'),
);
$response = $otsClient->putRow($request);

$request = array(
    'table_name' => 'MyTable', 'condition' => 'IGNORE',
    'primary_key' => array('PK0' => 3, 'PK1' => 'Guangdong'),
    'attribute_columns' => array('attr1' => 'Shenzhen'),
);
$response = $otsClient->putRow($request);



$request = array(
    'tables' => array(
        array(
            'table_name' => 'MyTable',
            'rows' => array(
                array('primary_key' => array('PK0' => 1, 'PK1' => 'Zhejiang')),
                array('primary_key' => array('PK0' => 2, 'PK1' => 'Jiangsu')),
                array('primary_key' => array('PK0' => 3, 'PK1' => 'Guangdong')),
            ),
            'columns_to_get' => array('PK1', 'attr1'),   // columns_to_get 参数用来指定要获取的列
        ),
    ),
);
$response = $otsClient->batchGetRow($request);
print json_encode($response);
