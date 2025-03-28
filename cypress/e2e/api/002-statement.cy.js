// error for wrong category
// 200 for right category
import { faker } from "@faker-js/faker";
import { generateStatementRequestBody } from "../../support/e2e";

const _ = Cypress._;
const url = `${Cypress.env("apiUrl")}/statement`;
const token = Cypress.env("token");
const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
};

context("Single statement endpoint", () => {
  const puid = faker.string.uuid();

  it("responds with 422 for empty request body", () => {
    cy.request({
      method: "POST",
      url: url,
      headers: {...headers, "Authorization": `Bearer ${token}`},
      failOnStatusCode: false,
      body: {}
    }).then((response) => {
      expect(response.status).to.eq(422);
    });
  });

  it("creates a new statement with correct data and returns 201 CREATED", () => {
    cy.fixture('statements').then((data) => {
      const body = generateStatementRequestBody(data, {'puid': puid});

      cy.request({
        method: "POST",
        url: url,
        headers: {...headers, "Authorization": `Bearer ${token}`},
        failOnStatusCode: false,
        body: body
      }).then((response) => {
        expect(response.status).to.eq(201);
        expect(response.body).to.have.property('id');
      });
    });
  });

  it("responds 422 for duplicate puid", () => {
    cy.fixture('statements').then((data) => {
      const body = generateStatementRequestBody(data, {'puid': puid});

      cy.request({
        method: "POST",
        url: url,
        headers: {...headers, "Authorization": `Bearer ${token}`},
        failOnStatusCode: false,
        body: body
      }).then((response) => {
        expect(response.status).to.eq(422);
        expect(response.body.message).to.eq("The identifier given is not unique within this platform.");
      });
    });
  });

  it("responds 422 for missing category field", () => {
    cy.fixture('statements').then((data) => {
      const body = generateStatementRequestBody(data, {'category': null});

      cy.request({
        method: "POST",
        url: url,
        headers: {...headers, "Authorization": `Bearer ${token}`},
        failOnStatusCode: false,
        body: body
      }).then((response) => {
        expect(response.status).to.eq(422);
        expect(response.body.message).to.eq("The category field is required.");
      });
    });
  });

  it('new category options work', () => {
    cy.fixture('statements').then((data) => {
      _.each(data.new_categories, (category) => {
        const body = generateStatementRequestBody(data, {'category': category});

        cy.request({
          method: "POST",
          url: url,
          headers: {...headers, "Authorization": `Bearer ${token}`},
          failOnStatusCode: false,
          body: body
        }).then((response) => {
          expect(response.status).to.eq(201);
          expect(response.body).to.have.property('id');
        });
      });
    });
  });

  it('new category_specification options work', () => {
    cy.fixture('statements').then((data) => {
      _.each(data.new_category_specifications, (spec) => {
        const body = generateStatementRequestBody(data, {'category_specification': [spec]});

        cy.request({
          method: "POST",
          url: url,
          headers: {...headers, "Authorization": `Bearer ${token}`},
          failOnStatusCode: false,
          body: body
        }).then((response) => {
          expect(response.status).to.eq(201);
          expect(response.body).to.have.property('id');
        });
      });
    });
  });

  it('deleted categories no longer work and produce a validation error', () => {
    cy.fixture('statements').then((data) => {
      _.each(data.deleted_categories, (category) => {
        const body = generateStatementRequestBody(data, {'category': category});

        cy.request({
          method: "POST",
          url: url,
          headers: {...headers, "Authorization": `Bearer ${token}`},
          failOnStatusCode: false,
          body: body
        }).then((response) => {
          expect(response.status).to.eq(422);
          expect(response.body.message).to.eq('The selected category is invalid.');
        });
      });
    });
  });

  it('deleted category specifications no longer work and produce a validation error', () => {
    cy.fixture('statements').then((data) => {
      _.each(data.deleted_category_specifications, (spec) => {
        const body = generateStatementRequestBody(data, {'category_specification': [spec]});

        cy.request({
          method: "POST",
          url: url,
          headers: {...headers, "Authorization": `Bearer ${token}`},
          failOnStatusCode: false,
          body: body
        }).then((response) => {
          expect(response.status).to.eq(422);
          expect(response.body.message).to.eq('The selected category specification is invalid.');
        });
      });
    });
  });

  it("renamed category options work with their new name and not their old one", () => {
    cy.fixture('statements').then((data) => {
      _.each(data.renamed_categories, (renamed) => {
        // Test old ones no longer work
        let body = generateStatementRequestBody(data, {'category': renamed[0]});

        cy.request({
          method: "POST",
          url: url,
          headers: {...headers, "Authorization": `Bearer ${token}`},
          failOnStatusCode: false,
          body: body
        }).then((response) => {
          expect(response.status).to.eq(422);
          expect(response.body.message).to.eq('The selected category is invalid.');
        });

        // Test new ones work
        body = generateStatementRequestBody(data, {'category': renamed[1]});

        cy.request({
          method: "POST",
          url: url,
          headers: {...headers, "Authorization": `Bearer ${token}`},
          failOnStatusCode: false,
          body: body
        }).then((response) => {
          expect(response.status).to.eq(201);
        });
      });
    });
  });

  it("renamed category_specification options work with their new name and not their old one", () => {
    cy.fixture('statements').then((data) => {
      _.each(data.renamed_category_specifications, (renamed) => {
        // Test old ones no longer work
        let body = generateStatementRequestBody(data, {'category_specification': [renamed[0]]});

        cy.request({
          method: "POST",
          url: url,
          headers: {...headers, "Authorization": `Bearer ${token}`},
          failOnStatusCode: false,
          body: body
        }).then((response) => {
          expect(response.status).to.eq(422);
          expect(response.body.message).to.eq('The selected category specification is invalid.');
        });

        // Test new ones work
        body = generateStatementRequestBody(data, {'category_specification': [renamed[1]]});

        cy.request({
          method: "POST",
          url: url,
          headers: {...headers, "Authorization": `Bearer ${token}`},
          failOnStatusCode: false,
          body: body
        }).then((response) => {
          expect(response.status).to.eq(201);
        });
      });
    });
  });

  it("accepts EAN-13 as content_id and returns 201 CREATED", () => {
    cy.fixture('statements').then((data) => {
      const ean = faker.commerce.isbn({ variant: 13, separator: '' });
      const body = generateStatementRequestBody(data, {'content_id': {
        'EAN-13': ean
      }});

      cy.request({
        method: "POST",
        url: url,
        headers: {...headers, "Authorization": `Bearer ${token}`},
        failOnStatusCode: false,
        body: body
      }).then((response) => {
        expect(response.status).to.eq(201);
        expect(response.body.content_id_ean).to.eq(ean);
      });
    });
  });
});
