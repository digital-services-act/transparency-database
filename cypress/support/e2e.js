// ***********************************************************
// This example support/e2e.js is processed and
// loaded automatically before your test files.
//
// This is a great place to put global configuration and
// behavior that modifies Cypress.
//
// You can change the location of this file or turn off
// automatically serving support files with the
// 'supportFile' configuration option.
//
// You can read more here:
// https://on.cypress.io/configuration
// ***********************************************************

// Import commands.js using ES2015 syntax:
import './commands';
import {faker} from '@faker-js/faker';

const _ = Cypress._;

export const generateStatementRequestBody = (data, overrides = {}) => ({
    "puid": faker.string.uuid(),
    "decision_visibility": [
      _.sample(data.decision_visibility),
    ],
    "decision_monetary": _.sample(data.decision_monetary),
    "decision_provision": _.sample(data.decision_provision),
    "decision_account": _.sample(data.decision_account),
    "account_type": _.sample(data.account_type),
    "decision_ground": "DECISION_GROUND_INCOMPATIBLE_CONTENT",
    "decision_ground_reference_url": faker.internet.url(),
    "incompatible_content_ground": faker.lorem.sentence(),
    "incompatible_content_explanation": faker.lorem.paragraph(),
    // "incompatible_content_illegal": _.sample("Yes", "No"),
    "incompatible_content_illegal": "Yes",
    "content_type": [
      ..._.sampleSize(data.content_type, _.sample([1, 2, 3])),
    ],
    "category": _.sample(data.categories),
    "category_specification": _.sampleSize(data.category_specifications, _.sample([1, 2, 3, 4])),
    "territorial_scope": [
      ..._.sampleSize(data.territorial_scope, _.sample([1, 2, 3, 4])),
    ],
    "content_language": _.sample(data.content_language),
    "content_date": faker.date.recent().toISOString().split("T")[0],
    "application_date": faker.date.recent().toISOString().split("T")[0],
    "end_date_monetary_restriction": faker.date.recent().toISOString().split("T")[0],
    "decision_facts": faker.lorem.paragraph(),
    "source_type": "SOURCE_TRUSTED_FLAGGER",
    "automated_detection": "No",
    "automated_decision": "AUTOMATED_DECISION_PARTIALLY",
    "end_date_visibility_restriction": null,
    "end_date_account_restriction": null,
    "end_date_service_restriction": null,
    "platform_name": "Platform 1",
    "permalink": faker.internet.url(),
    ...overrides
});
