<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%peer_assessment}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%users}}`
 * - `{{%group_info}}`
 */
class m210802_213327_create_peer_assessment_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%peer_assessment}}', [
            'id' => $this->primaryKey(),
            'student_id' => $this->integer(11)->notNull(),
            'completed' => $this->boolean(),
            'mark' => $this->string(5),
            'marked' => $this->boolean(),
            'group_id' => $this->integer(11),
        ]);

        // creates index for column `student_id`
        $this->createIndex(
            '{{%idx-peer_assessment-student_id}}',
            '{{%peer_assessment}}',
            'student_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-peer_assessment-student_id}}',
            '{{%peer_assessment}}',
            'student_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );

        // creates index for column `group_id`
        $this->createIndex(
            '{{%idx-peer_assessment-group_id}}',
            '{{%peer_assessment}}',
            'group_id'
        );

        // add foreign key for table `{{%group_info}}`
        $this->addForeignKey(
            '{{%fk-peer_assessment-group_id}}',
            '{{%peer_assessment}}',
            'group_id',
            '{{%group_info}}',
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
            '{{%fk-peer_assessment-student_id}}',
            '{{%peer_assessment}}'
        );

        // drops index for column `student_id`
        $this->dropIndex(
            '{{%idx-peer_assessment-student_id}}',
            '{{%peer_assessment}}'
        );

        // drops foreign key for table `{{%group_info}}`
        $this->dropForeignKey(
            '{{%fk-peer_assessment-group_id}}',
            '{{%peer_assessment}}'
        );

        // drops index for column `group_id`
        $this->dropIndex(
            '{{%idx-peer_assessment-group_id}}',
            '{{%peer_assessment}}'
        );

        $this->dropTable('{{%peer_assessment}}');
    }
}
