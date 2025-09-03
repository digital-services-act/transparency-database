import { faker } from "@faker-js/faker";
import { generateStatementRequestBody } from "../../support/e2e";

const _ = Cypress._;
const baseUrl = Cypress.env("baseUrl");
const statementFormUrl = `${baseUrl}/statement/create`;

context("Statement Form Page", () => {
  const puid = faker.string.uuid();

  before(() => {
    cy.loginLocal();
    // cy.loginToECAS();
  });

  beforeEach(() => {
    cy.visit(statementFormUrl);
    cy.wait(1500);
  });

  const fillNewStatementForm = (data, overrides = {}) => {
    const testData = generateStatementRequestBody(data, overrides);

    // Fill out the form
    cy.selectOptionsEcl('Select visibility decision', '#decision_visibility', ['Removal of content'])
      // .selectRandomOption('#decision_monetary')
      .selectRandomOption('#decision_provision')
      .selectRandomOption('#decision_account')
      .selectRandomOption('#account_type')
      // .selectRandomOption('#decision_ground')
      .selectOptionsEcl('Select content type', '#content_type', _.sampleSize(['App', 'Audio', 'Image', 'Text', 'Video']))
      .selectRandomOption('#content_language')
      .selectRandomOptionsEcl('Select category/categories', '#category_addition', 1, 3)
      .selectRandomOptionsEcl('Select keyword', '#category_specification', 1, 3)
      .selectRandomOption('#source_type')

    if (testData.category) {
      cy.selectRandomOption('#category');
    }

    cy.get('#decision_monetary').select('DECISION_MONETARY_TERMINATION');
    cy.get('#decision_ground').select('DECISION_GROUND_ILLEGAL_CONTENT');

    cy.get('#illegal_content_legal_ground').should('be.visible');
    cy.get('#illegal_content_legal_ground').type(faker.lorem.words(5));

    cy.get('#illegal_content_explanation').should('be.visible');
    cy.get('#illegal_content_explanation').type(faker.lorem.sentence());

    const contentDate = faker.date.recent({ days: 30 });

    cy.get('#decision_facts').type(faker.lorem.paragraph());
    cy.get('#decision_ground_reference_url').type(faker.internet.url());
    cy.get('#content_date').type(contentDate.toISOString().split('T')[0]);
    cy.get('#content_id_ean').type(faker.string.numeric(13));

    _.each(testData.territorial_scope, territory => {
      cy.get(`input[name="territorial_scope[]"][value="${territory}"]`).click({force: true});
    });

    cy.get('#application_date').type(faker.date.between({ from: contentDate, to: new Date() }).toISOString().split('T')[0]);

    cy.get(`input[name="automated_detection"][value="${testData.automated_detection}"]`)
      .click({ force: true });
    cy.get(`input[name="automated_decision"][value="${testData.automated_decision}"]`)
      .click({ force: true });

    cy.get('#puid').type(testData.puid);

    return cy.get('button').contains('Create the Statement of Reasons').click();
  };

  it.skip("shows validation error for empty form submission", () => {
    // cy.origin(baseUrl, () => {
      cy.get('button').contains('Create the Statement of Reasons').click(); // adjust selector

      // Check for validation errors
      cy.get('.ecl-notification--error').should('be.visible');
      cy.get('.ecl-notification--error').should('contain', 'Error');
      cy.get('.ecl-notification--error').should('contain', 'Your request contained multiple errors');
      cy.get('body').should('contain', 'The decision facts field is required.');
    // });
  });

  it("creates a new statement with correct data", () => {
    // cy.origin(baseUrl, () => {
      cy.fixture('statements').then((data) => {
        fillNewStatementForm(data, {puid: puid})
          .then(() => {
            cy.get('a').contains('Click here to view it').should('be.visible');
            cy.get('a').contains('Click here to view it').click().then(() => {
              cy.url().should('match', /\/statement\/\d+$/);
            });
          });
      });
    // });
  });

  it("shows validation error for duplicate puid", () => {
    cy.fixture('statements').then((data) => {
      fillNewStatementForm(data, {puid: puid})
        .then(() => {
          cy.get('.ecl-notification--error').should('be.visible');
          cy.get('.ecl-notification--error').should('contain', 'Error');
          cy.get('.ecl-notification--error').should('contain', 'The PUID is not unique in the database');
        });
    });
  });

  it("shows validation error when category is not selected", () => {
    cy.fixture('statements').then((data) => {
      fillNewStatementForm(data, {category: null})
        .then(() => {
          cy.get('.ecl-notification--error').should('be.visible');
          cy.get('.ecl-notification--error').should('contain', 'Error');
          cy.get('.ecl-feedback-message').contains('The category field is required').should('be.visible');
        });
    });
  });

  it('new category options are displayed', () => {
    const newCategs = [
      "Consumer information infringements",
      "Cyber violence",
      "Cyber violence against women",
      "Type of alleged illegal content not specified by the notifier",
    ];

    _.each(newCategs, (categ) => {
      cy.get('#category option').contains(categ).should('exist');
    });
  });

  it('old category options are not displayed', () => {
    const oldCategs = [
      "Non-consensual behaviour",
      "Pornography or sexualized content",
    ];


    _.each(oldCategs, (categ) => {
      cy.get('#category option').contains(categ).should('not.exist');
    });
  });

  it('renamed categories appear with their new name and not their old', () => {
    const renamedCategs =  [
      {
        old: "Unsafe and/or illegal products",
        new: "Unsafe, non-compliant or prohibited products",
      },
      {
        old: "Scope of platform service",
        new: "Other violation of provider’s terms and conditions",
      }
    ];

    _.each(renamedCategs, (categ) => {
      cy.get('#category option').contains(categ.old).should('not.exist');
      cy.get('#category option').contains(categ.new).should('exist');
    });
  });

  it('new category_specification options work', () => {
    const newSpecs = [
      "Cyber harassment",
      "Cyber harassment against women",
      "Cyber incitement to hatred or violence",
      "Cyber stalking",
      "Cyber stalking against women",
      "Gendered disinformation",
      "Hidden advertisement or commercial communication, including by influencers",
      "Illegal incitement to violence and hatred against women",
      "Cyber bullying and intimidation against girls",
      "Child sexual abuse material containing deepfake or similar technology",
      "Misleading information about the consumer's rights",
      "Misleading information about the characteristics of the goods and services",
      "Non-compliance with pricing regulations",
      "Prohibited or restricted products",
      "Trafficking in women and girls",
      "Unsafe or non-compliant products",
      "Violation of EU law relevant to civic discourse or elections",
      "Violation of national law relevant to civic discourse or elections",
      "Non-consensual (intimate) material sharing against women, including (image-based) sexual abuse against women (excluding content depicting minors)",
      "Non-consensual sharing of material containing deepfake or similar technology using a third party's features against women (excluding content depicting minors)",
    ];

    cy.get('button').contains('Select keyword').click()
      .then(() => {
        _.each(newSpecs, (spec) => {
          cy.get('#category_specification-dropdown').contains(spec).click();
        });
      });
  });

  it('deleted category specifications no longer appear in dropdown', () => {
    const deletedSpecs = [
      "Misinformation",
      "Foreign information manipulation and interference",
      "Regulated goods and services",
      "Dangerous toys",
      "Gender-based violence",
      "Image-based sexual abuse (excluding content depicting minors)",
    ];

    cy.get('button').contains('Select keyword').click()
      .then(() => {
        _.each(deletedSpecs, (spec) => {
          cy.get('#category_specification-dropdown').contains(spec).should('not.be', 'visible');
        });
      });
  });
});
