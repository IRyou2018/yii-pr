<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%group_info}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%assessments}}`
 */
class m210802_213056_create_group_info_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%group_info}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull(),
            'number' => $this->integer(2),
            'mark' => $this->string(5),
            'marked' => $this->boolean(),
            'assessment_id' => $this->integer(11),
        ]);

        // creates index for column `assessment_id`
        $this->createIndex(
            '{{%idx-group_info-assessment_id}}',
            '{{%group_info}}',
            'assessment_id'
        );

        // add foreign key for table `{{%assessments}}`
        $this->addForeignKey(
            '{{%fk-group_info-assessment_id}}',
            '{{%group_info}}',
            'assessment_id',
            '{{%assessments}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%assessments}}`
        $this->dropForeignKey(
            '{{%fk-group_info-assessment_id}}',
            '{{%group_info}}'
        );

        // drops index for column `assessment_id`
        $this->dropIndex(
            '{{%idx-group_info-assessment_id}}',
            '{{%group_info}}'
        );

        $this->dropTable('{{%group_info}}');
    }
}
