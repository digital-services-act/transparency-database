import { faker } from "@faker-js/faker";

const _ = Cypress._;
const url = Cypress.env("baseUrl");
const ecas = Cypress.env("ecasUrl");

Cypress.Commands.add('loginLocal', () => {
  cy.session(faker.string.uuid(), () => {
    cy.visit('/')
      .then(() => {
        cy.getCookies().then(cookies => {
          Cypress.env('laravelCookies', cookies);
          cy.visit('/');
        });
      });
  }, {
    cacheAcrossSpecs: true,
    validate: () => {
      const cookies = Cypress.env('laravelCookies') || [];
      _.each(cookies, cookie => {
        cy.setCookie(cookie.name, cookie.value, cookie);
      });

      expect(cookies.length).to.be.greaterThan(0);
      cy.getCookie('XSRF-TOKEN').should('exist');
    }
  });
});

Cypress.Commands.add('loginToECAS', () => {
  const username = Cypress.env('ecasUser');
  const password = Cypress.env('ecasPass');

  cy.session(faker.string.uuid(), () => {
    // cy.getCookies((preLoginCookies) => {
      cy.visit(url);

      cy.url().then((currentUrl) => {
        if (currentUrl.includes(ecas)) {
          // ECAS login page interactions
          cy.get('#username').type(username);
          cy.get('button').contains('Next').click();
          cy.get('#verif-method-dd-id').click()
            .then(() => {
              cy.get('#verif-method-dd-PASSWORD').click()
                .then(() => {
                  cy.get('#password').type(password);
                  cy.get('input[value="Sign in"]').click()
                    .then(() => {
                      cy.getCookies().then((postLoginCookies) => {
                        //__Secure-ECAS_SESSIONID,
                        Cypress.env('authCookies', postLoginCookies);

                        // cy.visit('/');

                        // cy.url({ timeout: 10000 }).should('include', url);
                      });
                    });
                });
            });
        }
      });
    // });
  }, {
    cacheAcrossSpecs: true,
    validate: () => {
      // Restore cookies before validation
      const authCookies = Cypress.env('authCookies') || [];
      authCookies.forEach(cookie => {
        cy.setCookie(cookie.name, cookie.value, cookie);
      });

      cy.getCookie('__Secure-ECAS_SESSIONID').should('exist');
      cy.visit(url);
    }
  });
});

Cypress.Commands.add('selectRandomOptionsEcl', (label, selector, start = 1, end = null) => {
  return cy.get(`${selector} option:not(:disabled)`).then(($options) => {
    const options = $options.toArray().map(el => el.textContent.trim());
    if (end > options.length) end = options.length;
    const randomOptions = Cypress._.sampleSize(options, start, end);

    cy.get('button').contains(label).click().then(() => {
      if (randomOptions.length > 1) {
        randomOptions.forEach(option => {
          cy.get(`${selector}-dropdown`).contains(option).click();
        });
      } else {
        cy.get(`${selector}-dropdown`).contains(randomOptions[0]).click();
      }

      cy.get('body').click(0, 0); // Close dropdown
    });
  });
});

Cypress.Commands.add('selectOptionsEcl', (label, selector, options = []) => {
  return cy.get('button').contains(label).click().then(() => {
    options.forEach(option => {
      cy.get(`${selector}-dropdown`).contains(option).click();
    });

    cy.get('body').click(0, 0); // Close dropdown
  });
});

Cypress.Commands.add('selectRandomOption', (selector) => {
  return cy.get(selector).then($select => {
    const options = [...$select[0].options]
      .filter(o => !o.disabled)  // skip disabled
      .map(o => o.value);

    const random = Cypress._.sample(options);
    cy.wrap($select).select(random);
  });
});

