<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%tasks_query}}`.
 */
class m210113_103404_create_tasks_queue_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%tasks_queue}}', [
            'id' => $this->primaryKey(),
            'classname' => $this->string(),
            'data' => $this->string(),
            'status' => $this->string(),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'restated_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP')
        ]);
        $this->createIndex('status_index', '{{%tasks_queue}}', 'status');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('status_index', '{{%tasks_queue}}');
        $this->dropTable('{{%tasks_queue}}');
    }
}
