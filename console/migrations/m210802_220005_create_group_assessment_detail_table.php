<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%group_assessment_detail}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 * - `{{%items}}`
 * - `{{%group_student_Info}}`
 */
class m210802_220005_create_group_assessment_detail_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%group_assessment_detail}}', [
            'id' => $this->primaryKey(),
            'work_student_id' => $this->integer(11)->notNull(),
            'mark' => $this->integer(3)->notNull(),
            'comment' => 'LONGTEXT',
            'contribution' => $this->integer(3)->notNull(),
            'item_id' => $this->integer(11)->notNull(),
            'group_student_Info_id' => $this->integer(11),
        ]);

        // creates index for column `work_student_id`
        $this->createIndex(
            '{{%idx-group_assessment_detail-work_student_id}}',
            '{{%group_assessment_detail}}',
            'work_student_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-group_assessment_detail-work_student_id}}',
            '{{%group_assessment_detail}}',
            'work_student_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );

        // creates index for column `item_id`
        $this->createIndex(
            '{{%idx-group_assessment_detail-item_id}}',
            '{{%group_assessment_detail}}',
            'item_id'
        );

        // add foreign key for table `{{%items}}`
        $this->addForeignKey(
            '{{%fk-group_assessment_detail-item_id}}',
            '{{%group_assessment_detail}}',
            'item_id',
            '{{%items}}',
            'id',
            'CASCADE'
        );

        // creates index for column `group_student_Info_id`
        $this->createIndex(
            '{{%idx-group_assessment_detail-group_student_Info_id}}',
            '{{%group_assessment_detail}}',
            'group_student_Info_id'
        );

        // add foreign key for table `{{%group_student_Info}}`
        $this->addForeignKey(
            '{{%fk-group_assessment_detail-group_student_Info_id}}',
            '{{%group_assessment_detail}}',
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
            '{{%fk-group_assessment_detail-work_student_id}}',
            '{{%group_assessment_detail}}'
        );

        // drops index for column `work_student_id`
        $this->dropIndex(
            '{{%idx-group_assessment_detail-work_student_id}}',
            '{{%group_assessment_detail}}'
        );

        // drops foreign key for table `{{%items}}`
        $this->dropForeignKey(
            '{{%fk-group_assessment_detail-item_id}}',
            '{{%group_assessment_detail}}'
        );

        // drops index for column `item_id`
        $this->dropIndex(
            '{{%idx-group_assessment_detail-item_id}}',
            '{{%group_assessment_detail}}'
        );

        // drops foreign key for table `{{%group_student_Info}}`
        $this->dropForeignKey(
            '{{%fk-group_assessment_detail-group_student_Info_id}}',
            '{{%group_assessment_detail}}'
        );

        // drops index for column `group_student_Info_id`
        $this->dropIndex(
            '{{%idx-group_assessment_detail-group_student_Info_id}}',
            '{{%group_assessment_detail}}'
        );

        $this->dropTable('{{%group_assessment_detail}}');
    }
}
