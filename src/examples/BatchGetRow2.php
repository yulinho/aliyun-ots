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
            'table_name' => 'MyTable1',      // 第一个表
            'rows' => array(
                array('primary_key' => array('PK0' => 1, 'PK1' => 'Zhejiang')),
                array('primary_key' => array('PK0' => 2, 'PK1' => 'Jiangsu')),
                array('primary_key' => array('PK0' => 3, 'PK1' => 'Guangdong')),
            ),
        ),
        array(
            'table_name' => 'MyTable2',     // 第二个表
            'rows' => array(
                array('primary_key' => array('PK0' => 4, 'PK1' => 'a')),
                array('primary_key' => array('PK0' => 5, 'PK1' => 'b')),
                array('primary_key' => array('PK0' => 6, 'PK1' => 'c')),
            ),
        ),
        array(
            'table_name' => 'MyTable3',     // 第三个表
            'rows' => array(
                array('primary_key' => array('PK0' => 7, 'PK1' => 'd')),
                array('primary_key' => array('PK0' => 8, 'PK1' => 'e')),
                array('primary_key' => array('PK0' => 9, 'PK1' => 'f')),
            ),
        ),


    ),
);
$response = $otsClient->batchGetRow($request);
print json_encode($response);

/* 样例输出：
{
    "tables": [
        {
            "table_name": "MyTable",
            "rows": [

                // 第一行的数据

                {
                    "is_ok": true,                  // 读取成功
                    "consumed": {
                        "capacity_unit": {
                            "read": 1,              // 这一行消耗了1个读CU
                            "write": 0
                        }
                    },
                    "row": {
                        "primary_key_columns": {
                            "PK0": 1,
                            "PK1": "Zhejiang"
                        },
                        "attribute_columns": {
                            "attr1": "Hangzhou"
                        }
                    }
                },

                // 第二行 ...
                // 第三行 ...
            ]
        },

        // 第二个表的数据 ...
        // 第三个表的数据 ...
    ]
}
*/

