<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%group_assessment_feedback}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%group_assessment}}`
 */
class m210827_185025_add_group_id_column_to_group_assessment_feedback_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%group_assessment_feedback}}', 'group_id', $this->integer());

        // creates index for column `group_id`
        $this->createIndex(
            '{{%idx-group_assessment_feedback-group_id}}',
            '{{%group_assessment_feedback}}',
            'group_id'
        );

        // add foreign key for table `{{%group_assessment}}`
        $this->addForeignKey(
            '{{%fk-group_assessment_feedback-group_id}}',
            '{{%group_assessment_feedback}}',
            'group_id',
            '{{%group_assessment}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%group_assessment}}`
        $this->dropForeignKey(
            '{{%fk-group_assessment_feedback-group_id}}',
            '{{%group_assessment_feedback}}'
        );

        // drops index for column `group_id`
        $this->dropIndex(
            '{{%idx-group_assessment_feedback-group_id}}',
            '{{%group_assessment_feedback}}'
        );

        $this->dropColumn('{{%group_assessment_feedback}}', 'group_id');
    }
}
