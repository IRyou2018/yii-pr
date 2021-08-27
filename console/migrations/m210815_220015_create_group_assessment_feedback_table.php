<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%group_assessment_feedback}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 * - `{{%items}}`
 * - `{{%group_student_Info}}`
 */
class m210815_220015_create_group_assessment_feedback_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%group_assessment_feedback}}', [
            'id' => $this->primaryKey(),
            'student_id' => $this->integer(11),
            'mark' => $this->integer(3)->notNull(),
            'comment' => 'LONGTEXT',
            'item_id' => $this->integer(11)->notNull(),
            'group_student_Info_id' => $this->integer(11),
        ]);

        // creates index for column `student_id`
        $this->createIndex(
            '{{%idx-group_assessment_feedback-student_id}}',
            '{{%group_assessment_feedback}}',
            'student_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-group_assessment_feedback-student_id}}',
            '{{%group_assessment_feedback}}',
            'student_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );

        // creates index for column `item_id`
        $this->createIndex(
            '{{%idx-group_assessment_feedback-item_id}}',
            '{{%group_assessment_feedback}}',
            'item_id'
        );

        // add foreign key for table `{{%items}}`
        $this->addForeignKey(
            '{{%fk-group_assessment_feedback-item_id}}',
            '{{%group_assessment_feedback}}',
            'item_id',
            '{{%items}}',
            'id',
            'CASCADE'
        );

        // creates index for column `group_student_Info_id`
        $this->createIndex(
            '{{%idx-group_assessment_feedback-group_student_Info_id}}',
            '{{%group_assessment_feedback}}',
            'group_student_Info_id'
        );

        // add foreign key for table `{{%group_student_Info}}`
        $this->addForeignKey(
            '{{%fk-group_assessment_feedback-group_student_Info_id}}',
            '{{%group_assessment_feedback}}',
            'group_student_Info_id',
            '{{%group_student_Info}}',
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
            '{{%fk-group_assessment_feedback-student_id}}',
            '{{%group_assessment_feedback}}'
        );

        // drops index for column `student_id`
        $this->dropIndex(
            '{{%idx-group_assessment_feedback-student_id}}',
            '{{%group_assessment_feedback}}'
        );

        // drops foreign key for table `{{%items}}`
        $this->dropForeignKey(
            '{{%fk-group_assessment_feedback-item_id}}',
            '{{%group_assessment_feedback}}'
        );

        // drops index for column `item_id`
        $this->dropIndex(
            '{{%idx-group_assessment_feedback-item_id}}',
            '{{%group_assessment_feedback}}'
        );

        // drops foreign key for table `{{%group_student_Info}}`
        $this->dropForeignKey(
            '{{%fk-group_assessment_feedback-group_student_Info_id}}',
            '{{%group_assessment_feedback}}'
        );

        // drops index for column `group_student_Info_id`
        $this->dropIndex(
            '{{%idx-group_assessment_feedback-group_student_Info_id}}',
            '{{%group_assessment_feedback}}'
        );

        $this->dropTable('{{%group_assessment_feedback}}');
    }
}
