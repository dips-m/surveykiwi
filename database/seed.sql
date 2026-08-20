START TRANSACTION;

INSERT INTO `users` (`name`,`email`,`password`,`status`,`created_at`,`updated_at`) VALUES
('SurveyKiwi Admin','admin@example.com','$2y$10$h.CfUn3SaztUgIttVPj8eOeHxr2ruDpwo2RIt4FaCMoDT7ZXHaK9y','active',NOW(),NOW());

INSERT INTO `surveys` (`id`,`title`,`description`,`status`,`created_at`,`updated_at`) VALUES
(1,'Customer Satisfaction Survey','Measure overall customer satisfaction and service experience.','published',NOW(),NOW()),
(2,'Employee Engagement Survey','Understand employee engagement, satisfaction and workplace experience.','published',NOW(),NOW()),
(3,'Product Feedback Survey','Collect customer feedback about product quality, usability and features.','published',NOW(),NOW()),
(4,'Website Experience Survey','Evaluate website usability, performance and overall user experience.','published',NOW(),NOW()),
(5,'Event Feedback Survey','Gather feedback from attendees about event quality and organization.','published',NOW(),NOW()),
(6,'Training Feedback Survey','Measure participant satisfaction and effectiveness of training programs.','published',NOW(),NOW()),
(7,'Healthcare Service Survey','Evaluate patient experience and healthcare service quality.','published',NOW(),NOW()),
(8,'Restaurant Feedback Survey','Understand customer satisfaction with food, service and ambiance.','published',NOW(),NOW()),
(9,'IT Support Survey','Measure user satisfaction with IT support and issue resolution.','published',NOW(),NOW()),
(10,'Customer Loyalty Survey','Understand customer loyalty, retention and recommendation behavior.','published',NOW(),NOW());

INSERT INTO `survey_questions` (`survey_id`,`question`,`type`,`options`,`settings`,`sort_order`,`created_at`,`updated_at`) VALUES
(1,'How satisfied are you with our overall service?','single_choice','["Very Satisfied","Satisfied","Neutral","Dissatisfied","Very Dissatisfied"]',NULL,1,NOW(),NOW()),
(1,'How would you rate the quality of our service?','rating','[1,2,3,4,5]',NULL,2,NOW(),NOW()),
(1,'How easy was it to get the help you needed?','single_choice','["Very Easy","Easy","Neutral","Difficult","Very Difficult"]',NULL,3,NOW(),NOW()),
(1,'How satisfied are you with our response time?','single_choice','["Very Satisfied","Satisfied","Neutral","Dissatisfied","Very Dissatisfied"]',NULL,4,NOW(),NOW()),
(1,'How likely are you to recommend us?','rating','[1,2,3,4,5,6,7,8,9,10]',NULL,5,NOW(),NOW()),
(1,'Which area should we improve?','single_choice','["Service","Support","Communication","Pricing","Product"]',NULL,6,NOW(),NOW()),
(1,'Did our service meet your expectations?','single_choice','["Yes","Partially","No"]',NULL,7,NOW(),NOW()),
(1,'Please share any additional feedback.','text',NULL,NULL,8,NOW(),NOW()),

(2,'How satisfied are you with your current role?','single_choice','["Very Satisfied","Satisfied","Neutral","Dissatisfied","Very Dissatisfied"]',NULL,1,NOW(),NOW()),
(2,'How would you rate communication within your team?','rating','[1,2,3,4,5]',NULL,2,NOW(),NOW()),
(2,'Do you feel valued by your organization?','single_choice','["Always","Often","Sometimes","Rarely","Never"]',NULL,3,NOW(),NOW()),
(2,'How satisfied are you with your manager?','single_choice','["Very Satisfied","Satisfied","Neutral","Dissatisfied","Very Dissatisfied"]',NULL,4,NOW(),NOW()),
(2,'Do you have opportunities for career growth?','single_choice','["Yes","Somewhat","No"]',NULL,5,NOW(),NOW()),
(2,'How would you rate your work-life balance?','rating','[1,2,3,4,5]',NULL,6,NOW(),NOW()),
(2,'Would you recommend this organization as a workplace?','single_choice','["Definitely","Probably","Not Sure","Probably Not","Definitely Not"]',NULL,7,NOW(),NOW()),
(2,'What can the organization improve?','text',NULL,NULL,8,NOW(),NOW()),

(3,'How satisfied are you with our product?','single_choice','["Very Satisfied","Satisfied","Neutral","Dissatisfied","Very Dissatisfied"]',NULL,1,NOW(),NOW()),
(3,'How easy is the product to use?','rating','[1,2,3,4,5]',NULL,2,NOW(),NOW()),
(3,'How would you rate product quality?','rating','[1,2,3,4,5]',NULL,3,NOW(),NOW()),
(3,'Which feature do you use most?','single_choice','["Dashboard","Reports","Search","Notifications","Other"]',NULL,4,NOW(),NOW()),
(3,'Does the product meet your needs?','single_choice','["Completely","Mostly","Partially","Not At All"]',NULL,5,NOW(),NOW()),
(3,'How reliable is the product?','single_choice','["Excellent","Good","Average","Poor"]',NULL,6,NOW(),NOW()),
(3,'Would you purchase this product again?','single_choice','["Definitely","Probably","Not Sure","Probably Not","Definitely Not"]',NULL,7,NOW(),NOW()),
(3,'What feature would you like us to add?','text',NULL,NULL,8,NOW(),NOW()),

(4,'How easy was it to navigate our website?','rating','[1,2,3,4,5]',NULL,1,NOW(),NOW()),
(4,'How would you rate the website design?','rating','[1,2,3,4,5]',NULL,2,NOW(),NOW()),
(4,'Did you find the information you needed?','single_choice','["Yes","Partially","No"]',NULL,3,NOW(),NOW()),
(4,'How fast did the website load?','single_choice','["Very Fast","Fast","Average","Slow","Very Slow"]',NULL,4,NOW(),NOW()),
(4,'How would you rate the mobile experience?','rating','[1,2,3,4,5]',NULL,5,NOW(),NOW()),
(4,'Was the website content clear?','single_choice','["Very Clear","Clear","Neutral","Unclear","Very Unclear"]',NULL,6,NOW(),NOW()),
(4,'Would you visit our website again?','single_choice','["Definitely","Probably","Not Sure","Probably Not","Definitely Not"]',NULL,7,NOW(),NOW()),
(4,'What should we improve on the website?','text',NULL,NULL,8,NOW(),NOW()),

(5,'How satisfied were you with the event?','single_choice','["Very Satisfied","Satisfied","Neutral","Dissatisfied","Very Dissatisfied"]',NULL,1,NOW(),NOW()),
(5,'How would you rate the event organization?','rating','[1,2,3,4,5]',NULL,2,NOW(),NOW()),
(5,'How would you rate the venue?','rating','[1,2,3,4,5]',NULL,3,NOW(),NOW()),
(5,'How useful were the sessions?','single_choice','["Very Useful","Useful","Neutral","Not Useful","Not Useful At All"]',NULL,4,NOW(),NOW()),
(5,'How satisfied were you with event communication?','single_choice','["Very Satisfied","Satisfied","Neutral","Dissatisfied","Very Dissatisfied"]',NULL,5,NOW(),NOW()),
(5,'Did the event meet your expectations?','single_choice','["Exceeded","Met","Partially Met","Did Not Meet"]',NULL,6,NOW(),NOW()),
(5,'Would you attend another event from us?','single_choice','["Definitely","Probably","Not Sure","Probably Not","Definitely Not"]',NULL,7,NOW(),NOW()),
(5,'What could we improve for future events?','text',NULL,NULL,8,NOW(),NOW()),

(6,'How satisfied are you with the training?','single_choice','["Very Satisfied","Satisfied","Neutral","Dissatisfied","Very Dissatisfied"]',NULL,1,NOW(),NOW()),
(6,'How would you rate the trainer?','rating','[1,2,3,4,5]',NULL,2,NOW(),NOW()),
(6,'How useful was the training content?','rating','[1,2,3,4,5]',NULL,3,NOW(),NOW()),
(6,'Was the training easy to understand?','single_choice','["Very Easy","Easy","Neutral","Difficult","Very Difficult"]',NULL,4,NOW(),NOW()),
(6,'Was the training duration appropriate?','single_choice','["Too Short","Appropriate","Too Long"]',NULL,5,NOW(),NOW()),
(6,'Did the training improve your knowledge?','single_choice','["Significantly","Moderately","Slightly","Not At All"]',NULL,6,NOW(),NOW()),
(6,'Would you recommend this training?','single_choice','["Definitely","Probably","Not Sure","Probably Not","Definitely Not"]',NULL,7,NOW(),NOW()),
(6,'What should be improved in the training?','text',NULL,NULL,8,NOW(),NOW()),

(7,'How satisfied are you with the healthcare service?','single_choice','["Very Satisfied","Satisfied","Neutral","Dissatisfied","Very Dissatisfied"]',NULL,1,NOW(),NOW()),
(7,'How would you rate staff behavior?','rating','[1,2,3,4,5]',NULL,2,NOW(),NOW()),
(7,'How would you rate waiting time?','rating','[1,2,3,4,5]',NULL,3,NOW(),NOW()),
(7,'Was the facility clean and comfortable?','single_choice','["Excellent","Good","Average","Poor"]',NULL,4,NOW(),NOW()),
(7,'Did the staff explain everything clearly?','single_choice','["Always","Often","Sometimes","Rarely","Never"]',NULL,5,NOW(),NOW()),
(7,'How satisfied are you with the appointment process?','single_choice','["Very Satisfied","Satisfied","Neutral","Dissatisfied","Very Dissatisfied"]',NULL,6,NOW(),NOW()),
(7,'Would you recommend our healthcare service?','single_choice','["Definitely","Probably","Not Sure","Probably Not","Definitely Not"]',NULL,7,NOW(),NOW()),
(7,'How can we improve your experience?','text',NULL,NULL,8,NOW(),NOW()),

(8,'How satisfied are you with your meal?','single_choice','["Very Satisfied","Satisfied","Neutral","Dissatisfied","Very Dissatisfied"]',NULL,1,NOW(),NOW()),
(8,'How would you rate the food quality?','rating','[1,2,3,4,5]',NULL,2,NOW(),NOW()),
(8,'How would you rate the staff service?','rating','[1,2,3,4,5]',NULL,3,NOW(),NOW()),
(8,'How would you rate the restaurant ambiance?','rating','[1,2,3,4,5]',NULL,4,NOW(),NOW()),
(8,'Was your order served on time?','single_choice','["Yes","Partially","No"]',NULL,5,NOW(),NOW()),
(8,'How would you rate the value for money?','single_choice','["Excellent","Good","Average","Poor"]',NULL,6,NOW(),NOW()),
(8,'Would you visit our restaurant again?','single_choice','["Definitely","Probably","Not Sure","Probably Not","Definitely Not"]',NULL,7,NOW(),NOW()),
(8,'What can we improve?','text',NULL,NULL,8,NOW(),NOW()),

(9,'How satisfied are you with IT support?','single_choice','["Very Satisfied","Satisfied","Neutral","Dissatisfied","Very Dissatisfied"]',NULL,1,NOW(),NOW()),
(9,'How quickly was your issue resolved?','single_choice','["Very Quickly","Quickly","Average","Slowly","Very Slowly"]',NULL,2,NOW(),NOW()),
(9,'How would you rate the support team?','rating','[1,2,3,4,5]',NULL,3,NOW(),NOW()),
(9,'Was your issue resolved completely?','single_choice','["Yes","Partially","No"]',NULL,4,NOW(),NOW()),
(9,'How easy was it to contact IT support?','rating','[1,2,3,4,5]',NULL,5,NOW(),NOW()),
(9,'Was the communication clear?','single_choice','["Very Clear","Clear","Neutral","Unclear","Very Unclear"]',NULL,6,NOW(),NOW()),
(9,'Would you recommend our IT support service?','single_choice','["Definitely","Probably","Not Sure","Probably Not","Definitely Not"]',NULL,7,NOW(),NOW()),
(9,'What should IT support improve?','text',NULL,NULL,8,NOW(),NOW()),

(10,'How likely are you to recommend our company?','rating','[1,2,3,4,5,6,7,8,9,10]',NULL,1,NOW(),NOW()),
(10,'How satisfied are you with our products?','rating','[1,2,3,4,5]',NULL,2,NOW(),NOW()),
(10,'How satisfied are you with our service?','rating','[1,2,3,4,5]',NULL,3,NOW(),NOW()),
(10,'How likely are you to purchase from us again?','single_choice','["Definitely","Probably","Not Sure","Probably Not","Definitely Not"]',NULL,4,NOW(),NOW()),
(10,'How well does our company meet your expectations?','single_choice','["Exceeds","Meets","Partially Meets","Does Not Meet"]',NULL,5,NOW(),NOW()),
(10,'What is the main reason you continue using us?','single_choice','["Quality","Price","Service","Convenience","Trust"]',NULL,6,NOW(),NOW()),
(10,'How would you rate your overall experience?','rating','[1,2,3,4,5]',NULL,7,NOW(),NOW()),
(10,'What could we do to improve your loyalty?','text',NULL,NULL,8,NOW(),NOW());

COMMIT;