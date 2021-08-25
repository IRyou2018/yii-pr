<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%group_student_Info}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%users}}`
 * - `{{%group_assessment}}`
 */
class m210802_213327_create_group_student_Info_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%group_student_Info}}', [
            'id' => $this->primaryKey(),
            'student_id' => $this->integer(11)->notNull(),
            'completed' => $this->boolean(),
            'mark' => $this->integer(3),
            'marked' => $this->boolean(),
            'group_id' => $this->integer(11),
        ]);

        // creates index for column `student_id`
        $this->createIndex(
            '{{%idx-group_student_Info-student_id}}',
            '{{%group_student_Info}}',
            'student_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-group_student_Info-student_id}}',
            '{{%group_student_Info}}',
            'student_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );

        // creates index for column `group_id`
        $this->createIndex(
            '{{%idx-group_student_Info-group_id}}',
            '{{%group_student_Info}}',
            'group_id'
        );

        // add foreign key for table `{{%group_assessment}}`
        $this->addForeignKey(
            '{{%fk-group_student_Info-group_id}}',
            '{{%group_student_Info}}',
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
        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-group_student_Info-student_id}}',
            '{{%group_student_Info}}'
        );

        // drops index for column `student_id`
        $this->dropIndex(
            '{{%idx-group_student_Info-student_id}}',
            '{{%group_student_Info}}'
        );

        // drops foreign key for table `{{%group_assessment}}`
        $this->dropForeignKey(
            '{{%fk-group_student_Info-group_id}}',
            '{{%group_student_Info}}'
        );

        // drops index for column `group_id`
        $this->dropIndex(
            '{{%idx-group_student_Info-group_id}}',
            '{{%group_student_Info}}'
        );

        $this->dropTable('{{%group_student_Info}}');
    }
}
