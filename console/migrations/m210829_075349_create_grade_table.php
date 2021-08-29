<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%grade}}`.
 */
class m210829_075349_create_grade_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%grade}}', [
            'id' => $this->primaryKey(),
            'grade' => $this->string(30)->notNull(),
            'weight' => $this->integer()->notNull(),
            'min_mark' => $this->integer()->notNull(),
            'max_mark' => $this->integer()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%grade}}');
    }
}
