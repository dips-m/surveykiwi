-- 1. Insert Surveys first (Parent Table)
INSERT INTO `surveys` (`id`, `public_token`, `title`, `description`, `end_date`, `status`, `created_at`, `updated_at`) VALUES
(1, 'dfa57379-a869-463e-8979-62008cd4e874', 'School Experience Feedback Form', 'We value your input to help us improve the learning environment and overall experience at our school.', '2026-08-31 22:00:00', 'published', '2026-07-27 11:25:57', '2026-08-04 12:55:59'),
(2, '9f6ff7e4-dbf2-4046-94e6-16b871360caa', 'Survey: VTP property visit', 'Demo survey about real estate visiting a VTP property.', NULL, 'published', '2026-07-30 06:01:04', '2026-07-30 06:01:40'),
(3, '0ada7d17-6b67-4d49-a0da-8eb17b633a67', 'VTP Property Customer Satisfaction Survey', 'Help us understand your experience and satisfaction with VTP Property developments.', NULL, 'published', '2026-07-30 11:36:39', '2026-07-30 12:26:08');

-- 2. Insert Survey Questions (Child Table - Now safe because Surveys exist)

INSERT INTO `survey_questions` (`id`, `survey_id`, `question`, `type`, `options`, `settings`, `sort_order`, `created_at`, `updated_at`) VALUES
-- Survey 1: School Experience Feedback Form (10 Questions)
(1, 1, 'Overall, how satisfied are you with your learning experience at our school?', 'rating', NULL, '{\"max\": 5, \"min\": 1, \"required\": true}', 1, '2026-07-27 11:28:00', '2026-07-27 11:28:00'),
(2, 1, 'How would you rate the cleanliness and upkeep of the school classrooms and facilities?', 'rating', NULL, '{\"max\": 5, \"min\": 1, \"required\": true}', 2, '2026-07-27 11:28:00', '2026-07-27 11:28:00'),
(3, 1, 'How satisfied are you with the responsiveness and effectiveness of our teaching staff?', 'rating', NULL, '{\"max\": 5, \"min\": 1, \"required\": true}', 3, '2026-07-27 11:28:00', '2026-07-27 11:28:00'),
(4, 1, 'How would you describe your experience with the school administration staff?', 'radio', '[\"Excellent\", \"Good\", \"Average\", \"Poor\", \"Very Poor\"]', '{\"max\": null, \"min\": null, \"required\": true}', 4, '2026-07-27 11:28:00', '2026-07-27 11:28:00'),
(5, 1, 'Are you satisfied with the level of safety and security provided on campus?', 'radio', '[\"Very Satisfied\", \"Satisfied\", \"Neutral\", \"Dissatisfied\", \"Very Dissatisfied\"]', '{\"max\": null, \"min\": null, \"required\": true}', 5, '2026-07-27 11:28:00', '2026-07-27 11:28:00'),
(6, 1, 'Which of the following extracurricular activities are most important to you?', 'checkbox', '[\"Sports\", \"Arts and Music\", \"Science Club\", \"Debate Society\", \"Community Service\"]', '{\"max\": null, \"min\": null, \"required\": false}', 6, '2026-07-27 11:28:00', '2026-07-27 11:28:00'),
(7, 1, 'How well do you feel informed about school events, schedules, and policy updates?', 'dropdown', '[\"Always well-informed\", \"Usually well-informed\", \"Sometimes informed\", \"Rarely informed\", \"Never informed\"]', '{\"max\": null, \"min\": null, \"required\": true}', 7, '2026-07-27 11:28:00', '2026-07-27 11:28:00'),
(8, 1, 'Do you plan to continue your enrollment for the next academic year?', 'radio', '[\"Yes, definitely\", \"Likely yes\", \"Unsure\", \"Likely no\", \"No, definitely not\"]', '{\"max\": null, \"min\": null, \"required\": true}', 8, '2026-07-27 11:28:00', '2026-07-27 11:28:00'),
(9, 1, 'What is one change the school could make to significantly improve your learning experience?', 'textarea', NULL, '{\"max\": null, \"min\": null, \"required\": false}', 9, '2026-07-27 11:28:00', '2026-07-27 11:28:00'),
(10, 1, 'How likely are you to recommend this school to a friend or family member?', 'rating', NULL, '{\"max\": 5, \"min\": 1, \"required\": true}', 10, '2026-07-27 11:28:00', '2026-07-27 11:28:00'),

-- Survey 2: VTP Property Visit Survey (6 Questions)
(11, 2, 'How was your overall experience during your visit to the VTP property?', 'rating', NULL, '{\"max\": 5, \"min\": 1, \"required\": true}', 1, '2026-07-30 06:05:00', '2026-07-30 06:05:00'),
(12, 2, 'How would you rate the knowledge and helpfulness of our sales executive?', 'rating', NULL, '{\"max\": 5, \"min\": 1, \"required\": true}', 2, '2026-07-30 06:05:00', '2026-07-30 06:05:00'),
(13, 2, 'How did you find out about this VTP property?', 'dropdown', '[\"Social Media\", \"Property Portal\", \"Newspaper Ad\", \"Word of Mouth\", \"Walk-in\"]', '{\"max\": null, \"min\": null, \"required\": true}', 3, '2026-07-30 06:05:00', '2026-07-30 06:05:00'),
(14, 2, 'Which configuration type are you primarily interested in?', 'radio', '[\"1 BHK\", \"2 BHK\", \"3 BHK\", \"Villa / Penthouse\"]', '{\"max\": null, \"min\": null, \"required\": true}', 4, '2026-07-30 06:05:00', '2026-07-30 06:05:00'),
(15, 2, 'What are your main expectations from a residential property?', 'checkbox', '[\"Location & Connectivity\", \"Amenities\", \"Pricing & Payment Plans\", \"Builder Reputation\"]', '{\"max\": null, \"min\": null, \"required\": false}', 5, '2026-07-30 06:05:00', '2026-07-30 06:05:00'),
(16, 2, 'Please share any additional comments or queries regarding your visit.', 'textarea', NULL, '{\"max\": null, \"min\": null, \"required\": false}', 6, '2026-07-30 06:05:00', '2026-07-30 06:05:00'),

-- Survey 3: VTP Property Customer Satisfaction Survey (7 Questions)
(17, 3, 'How satisfied are you with the quality of construction of your property?', 'rating', NULL, '{\"max\": 5, \"min\": 1, \"required\": true}', 1, '2026-07-30 11:40:00', '2026-07-30 11:40:00'),
(18, 3, 'How would you rate the handling of the handover and documentation process?', 'rating', NULL, '{\"max\": 5, \"min\": 1, \"required\": true}', 2, '2026-07-30 11:40:00', '2026-07-30 11:40:00'),
(19, 3, 'How responsive has our customer support team been to your queries or issues?', 'radio', '[\"Very Responsive\", \"Responsive\", \"Neutral\", \"Slow\", \"Unresponsive\"]', '{\"max\": null, \"min\": null, \"required\": true}', 3, '2026-07-30 11:40:00', '2026-07-30 11:40:00'),
(20, 3, 'Are you satisfied with the maintenance services provided post-possession?', 'radio', '[\"Yes, highly satisfied\", \"Moderately satisfied\", \"Neutral\", \"Dissatisfied\"]', '{\"max\": null, \"min\": null, \"required\": true}', 4, '2026-07-30 11:40:00', '2026-07-30 11:40:00'),
(21, 3, 'Which community facilities do you use most frequently?', 'checkbox', '[\"Clubhouse\", \"Swimming Pool\", \"Kids Play Area\", \"Walking Track\", \"Security Systems\"]', '{\"max\": null, \"min\": null, \"required\": false}', 5, '2026-07-30 11:40:00', '2026-07-30 11:40:00'),
(22, 3, 'Do you have any suggestions to improve our society management?', 'textarea', NULL, '{\"max\": null, \"min\": null, \"required\": false}', 6, '2026-07-30 11:40:00', '2026-07-30 11:40:00'),
(23, 3, 'How likely are you to purchase another property or recommend VTP to others?', 'rating', NULL, '{\"max\": 5, \"min\": 1, \"required\": true}', 7, '2026-07-30 11:40:00', '2026-07-30 11:40:00');