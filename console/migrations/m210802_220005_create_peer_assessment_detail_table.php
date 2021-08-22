<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%peer_assessment_detail}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 * - `{{%items}}`
 * - `{{%peer_assessment}}`
 */
class m210802_220005_create_peer_assessment_detail_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%peer_assessment_detail}}', [
            'id' => $this->primaryKey(),
            'work_student_id' => $this->integer(11)->notNull(),
            'mark' => $this->integer(3)->notNull(),
            'comment' => 'LONGTEXT',
            'contribution' => $this->integer(3)->notNull(),
            'item_id' => $this->integer(11)->notNull(),
            'peer_assessment_id' => $this->integer(11),
        ]);

        // creates index for column `work_student_id`
        $this->createIndex(
            '{{%idx-peer_assessment_detail-work_student_id}}',
            '{{%peer_assessment_detail}}',
            'work_student_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-peer_assessment_detail-work_student_id}}',
            '{{%peer_assessment_detail}}',
            'work_student_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );

        // creates index for column `item_id`
        $this->createIndex(
            '{{%idx-peer_assessment_detail-item_id}}',
            '{{%peer_assessment_detail}}',
            'item_id'
        );

        // add foreign key for table `{{%items}}`
        $this->addForeignKey(
            '{{%fk-peer_assessment_detail-item_id}}',
            '{{%peer_assessment_detail}}',
            'item_id',
            '{{%items}}',
            'id',
            'CASCADE'
        );

        // creates index for column `peer_assessment_id`
        $this->createIndex(
            '{{%idx-peer_assessment_detail-peer_assessment_id}}',
            '{{%peer_assessment_detail}}',
            'peer_assessment_id'
        );

        // add foreign key for table `{{%peer_assessment}}`
        $this->addForeignKey(
            '{{%fk-peer_assessment_detail-peer_assessment_id}}',
            '{{%peer_assessment_detail}}',
            'peer_assessment_id',
            '{{%peer_assessment}}',
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
            '{{%fk-peer_assessment_detail-work_student_id}}',
            '{{%peer_assessment_detail}}'
        );

        // drops index for column `work_student_id`
        $this->dropIndex(
            '{{%idx-peer_assessment_detail-work_student_id}}',
            '{{%peer_assessment_detail}}'
        );

        // drops foreign key for table `{{%items}}`
        $this->dropForeignKey(
            '{{%fk-peer_assessment_detail-item_id}}',
            '{{%peer_assessment_detail}}'
        );

        // drops index for column `item_id`
        $this->dropIndex(
            '{{%idx-peer_assessment_detail-item_id}}',
            '{{%peer_assessment_detail}}'
        );

        // drops foreign key for table `{{%peer_assessment}}`
        $this->dropForeignKey(
            '{{%fk-peer_assessment_detail-peer_assessment_id}}',
            '{{%peer_assessment_detail}}'
        );

        // drops index for column `peer_assessment_id`
        $this->dropIndex(
            '{{%idx-peer_assessment_detail-peer_assessment_id}}',
            '{{%peer_assessment_detail}}'
        );

        $this->dropTable('{{%peer_assessment_detail}}');
    }
}
