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

//$otsClient->getClientConfig()->errorLogHandler = null;
//$otsClient->getClientConfig()->debugLogHandler = null;

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
            'read' => 5000,
            'write' => 5000,
        ),
    ),
);
$otsClient->createTable($request);
sleep(10);


for ($i = 0; $i < 6000; $i ++) {
    $request = array(
        'table_name' => 'MyTable',
        'condition' => 'IGNORE',         // condition可以为IGNORE, EXPECT_EXIST, EXPECT_NOT_EXIST
        'primary_key' => array(          // 主键
            'PK0' => $i,
            'PK1' => 'abc',
        ),
        'attribute_columns' => array(    // 属性
            'attr0' => 456,                 // INTEGER类型
            'attr1' => 'Hangzhou',          // STRING类型
            'attr2' => 3.14,                // DOUBLE类型
            'attr3' => true,                // BOOLEAN类型
        )
    );
    $otsClient->putRow($request);
}


// 请注意，这个例子运行时PHP占用内存较大，在我们的测试环境中，需要将php.ini中的
// memory_limit 设置为 256M

$startPK = array(
    'PK0' => array('type' => 'INF_MIN'),         // array('type' => 'INF_MIN') 用来表示最小值
    'PK1' => array('type' => 'INF_MIN'),
);

$endPK = array(
    'PK0' => array('type' => 'INF_MAX'),         // array('type' => 'INF_MAX') 用来表示最小值
    'PK1' => array('type' => 'INF_MAX'),
);

while (!empty($startPK)) {

    $request = array(
        'table_name' => 'MyTable',
        'direction' => 'FORWARD',                          // 方向可以为 FORWARD 或者 BACKWARD 
        'inclusive_start_primary_key' => $startPK,         // 开始主键
        'exclusive_end_primary_key' => $endPK,             // 结束主键
        'columns_to_get' => array('attr0', 'attr1'),       // 指定获取 attr0 和 attr1 两列
    );
    
    $response = $otsClient->getRange($request);

    print "Read CU Consumed: {$response['consumed']['capacity_unit']['read']}\n";
    
    foreach ($response['rows'] as $rowData) {
        // 处理每一行数据
    }

    // 如果 next_start_primary_key 不为空，则代表
    // 范围内还有数据，需要继续读取
    $startPK = $response['next_start_primary_key'];
}

/* 单次GetRange的样例输出：

{
    "consumed": {
        "capacity_unit": {
            "read": 303,
            "write": 0
        }
    },
    "next_start_primary_key": [],
    "rows": [
        {
            "primary_key_columns": [],
            "attribute_columns": {
                "attr0": 456,
                "attr1": "Hangzhou"
            }
        },
        {
            "primary_key_columns": [],
            "attribute_columns": {
                "attr0": 456,
                "attr1": "Hangzhou"
            }
        },
        {
            "primary_key_columns": [],
            "attribute_columns": {
                "attr0": 456,
                "attr1": "Hangzhou"
            }
        },

        // 更多行 。。。
    ]
}

*/

