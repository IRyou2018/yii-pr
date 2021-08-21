<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%peer_review}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 * - `{{%individual_assessment}}`
 */
class m210802_120651_create_peer_review_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%peer_review}}', [
            'id' => $this->primaryKey(),
            'marker_student_id' => $this->integer(11)->notNull(),
            'individual_assessment_id' => $this->integer(11)->notNull(),
            'marked' => $this->boolean(),
        ]);

        // creates index for column `marker_student_id`
        $this->createIndex(
            '{{%idx-peer_review-marker_student_id}}',
            '{{%peer_review}}',
            'marker_student_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-peer_review-marker_student_id}}',
            '{{%peer_review}}',
            'marker_student_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );

        // creates index for column `individual_assessment_id`
        $this->createIndex(
            '{{%idx-peer_review-individual_assessment_id}}',
            '{{%peer_review}}',
            'individual_assessment_id'
        );

        // add foreign key for table `{{%individual_assessment}}`
        $this->addForeignKey(
            '{{%fk-peer_review-individual_assessment_id}}',
            '{{%peer_review}}',
            'individual_assessment_id',
            '{{%individual_assessment}}',
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
            '{{%fk-peer_review-marker_student_id}}',
            '{{%peer_review}}'
        );

        // drops index for column `marker_student_id`
        $this->dropIndex(
            '{{%idx-peer_review-marker_student_id}}',
            '{{%peer_review}}'
        );

        // drops foreign key for table `{{%individual_assessment}}`
        $this->dropForeignKey(
            '{{%fk-peer_review-individual_assessment_id}}',
            '{{%peer_review}}'
        );

        // drops index for column `individual_assessment_id`
        $this->dropIndex(
            '{{%idx-peer_review-individual_assessment_id}}',
            '{{%peer_review}}'
        );

        $this->dropTable('{{%peer_review}}');
    }
}
