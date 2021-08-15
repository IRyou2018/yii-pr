<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%assessments}}`.
 */
class m210802_110752_create_assessments_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%assessments}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull(),
            'assessment_type' => $this->boolean(),
            'deadline' => $this->integer()->notNull(),
            'active' => $this->boolean()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'created_by' => $this->integer(11)->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'updated_by' => $this->integer(11)->notNull(),
        ]);

         // creates index for column `created_by`
         $this->createIndex(
            '{{%idx-assessments-created_by}}',
            '{{%assessments}}',
            'created_by'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-assessments-created_by}}',
            '{{%assessments}}',
            'created_by',
            '{{%user}}',
            'id',
            'CASCADE'
        );

        // creates index for column `updated_by`
        $this->createIndex(
            '{{%idx-assessments-updated_by}}',
            '{{%assessments}}',
            'updated_by'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-assessments-updated_by}}',
            '{{%assessments}}',
            'updated_by',
            '{{%user}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-products-created_by}}',
            '{{%products}}'
        );

        // drops index for column `created_by`
        $this->dropIndex(
            '{{%idx-products-created_by}}',
            '{{%products}}'
        );

        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-products-updated_by}}',
            '{{%products}}'
        );

        // drops index for column `updated_by`
        $this->dropIndex(
            '{{%idx-products-updated_by}}',
            '{{%products}}'
        );
        
        $this->dropTable('{{%assessments}}');
    }
}
