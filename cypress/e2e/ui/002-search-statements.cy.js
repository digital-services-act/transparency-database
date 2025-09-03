import { faker } from "@faker-js/faker";
import { generateStatementRequestBody } from "../../support/e2e";

/**
 * Additional Test Ideas:
Combination filters: Test combinations of filters (e.g., platform + category + date range)

[x] Pagination: If results are paginated, test pagination controls


[x] Empty results: Test behavior when no results match the filters
Error handling: Test invalid inputs (e.g., invalid date formats)

Mobile responsiveness: Test form behavior on different screen sizes

Accessibility: Add accessibility tests (tab navigation, ARIA attributes)

Performance: Test loading times with different filter combinations

Saved searches: If the application supports saving searches, test that functionality

Export functionality: If results can be exported, test export options
 */

Cypress.Cookies.debug(true);

const _ = Cypress._;
const url = Cypress.env('baseUrl');

context("Search statements form", () => {
  let initialResultsCount = 0;

  // before(() => {
  //   cy.loginToECAS();
  // });

  beforeEach(() => {
    cy.visit(url).then(() => {
      cy.visit('/statement');
    });
  });

  it('should load the search form', () => {
    cy.origin(url, () => {
      cy.contains('Search “Statements of reasons”').should('be.visible');
      cy.get('form[method="get"]').should('exist');
      cy.get('#s').should('be.visible');

      cy.get('p').contains('Showing 1 to').invoke('text')
        .then((text) => {
          const match = text.match(/of\s+(\d+)\s+results/i)?.[1];
          initialResultsCount = parseInt(match);
          expect(initialResultsCount).to.be.gt(0);
        });
    });
  });

  it('pagination should work', () => {
    cy.get('table.ecl-table.ecl-table--zebra tbody tr').first().click()
      .then(() => {
        cy.get('h1').contains('Statement of reason details:').invoke('text')
          .then((text) => {
            const uuid = text.match(/([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})/)[0];

            cy.visit(`${url}/statement`);
            cy.get('.ecl-pagination__item--next').click()
              .then(() => {
                cy.get('table.ecl-table.ecl-table--zebra tbody tr').first().click()
                  .then(() => {
                    cy.get('h1').contains('Statement of reason details:').invoke('text')
                      .then((newText) => {
                        const nextUuid = newText.match(/([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})/)[0];

                        expect(uuid).to.not.eq(nextUuid);
                      });
                  });
              });
          });
      });
  });

  it.skip('table should be empty for search string with no results', () => {
    cy.get('#s').type('xxxxxxx');
    cy.get('button').contains('Show results').click()
      .then(() => {
        cy.get('table.ecl-table.ecl-table--zebra').should('be.visible');
        cy.get('table.ecl-table.ecl-table--zebra tbody tr').should('have.length', 0);
      });
  });

  it.skip('should search by UUID and retrieve one result', () => {
    const uuid = 'ae746f0f-406f-4aef-af23-1879cd43e6ec';

    cy.get('#s').type(uuid);
    cy.get('button').contains('Show results').click()
      .then(() => {
        cy.get('table.ecl-table.ecl-table--zebra').should('be.visible')
        cy.get('table.ecl-table.ecl-table--zebra tbody tr').should('have.length', 1)
        cy.get('table.ecl-table.ecl-table--zebra tbody tr').first().click()
          .then(() => {
            cy.contains(`Statement of reason details: ${uuid}`).should('be.visible')
          })
      });
  });

  it.skip('should filter by random number of platforms', () => {
    cy.contains('Select one or more platforms').click()
      .then(() => {
        cy.get('#platform_id option:not(:disabled)').then(($options) => {
          const platforms = $options.toArray().map(el => el.textContent.trim());
          const randomPlatforms = _.sampleSize(platforms, _.random(1, 5));

          randomPlatforms.forEach(platform => cy.get('#platform_id-dropdown').contains(platform).click());

          cy.get('body').click(0, 0);
          cy.get('button').contains('Show results').click().then(() => {
            cy.get('p').contains('Showing 1 to').invoke('text')
              .then((text) => {
                const match = text.split('of')[1].match(/(\d+)/)[0];
                const filteredCount = parseInt(match[1]);
                expect(filteredCount).to.be.lt(initialResultsCount);
              });
          });
        });
      });
  });

  it.skip('should filter by category and verify results', () => {
    cy.get('#category option:not(:disabled)').then(($options) => {
      const categories = $options.toArray().map(el => el.textContent.trim());
      const category = _.sample(categories);

      cy.contains('Select one or more categories').click().then(() => {
        cy.get('#category-dropdown').contains(category).click();
        cy.get('body').click(0, 0);
        cy.get('button').contains('Show results').click()
          .then(() => {
            cy.get('table.ecl-table.ecl-table--zebra tbody tr').should('contain', category);
          });
      });
    });
  });

  it.skip('should reset search results when reset filter is pressed', () => {
    cy.get('button').contains('Reset filters').click()
      .then(() => {
       cy.get('p').contains('Showing 1 to').invoke('text')
          .then((text) => {
            const match = text.split('of')[1].match(/(\d+)/)[0];
            const resetCount = parseInt(match[1]);
            expect(resetCount).to.eq(initialResultsCount);
          });
      });
  });

  it.skip('should contain v2 new categories and filter by them', () => {
    cy.fixture('statements').then((data) => {
      _.each(data.new_categories, (category) => {
        cy.get(`option[value="${category}"]`).invoke('text').then((categoryText) => {
          cy.get('button').contains('Reset filters').click()
          .then(() => {
            cy.contains('Select one or more categories').click().then(() => {
              cy.get('#category-dropdown').contains(categoryText).click();
              cy.get('body').click(0, 0);
              cy.get('button').contains('Show results').click()
                .then(() => {
                  cy.get('table.ecl-table.ecl-table--zebra tbody tr').should('contain', categoryText);
                });
            });
          });
        });
      });
    })
  });

  it.skip('should contain renamed categories and filter by them', () => {
    cy.fixture('statements').then((data) => {
      _.each(data.renamed_categories, (category) => {
        cy.get(`option[value="${category[0]}"]`).should('not.be', 'visible');
        cy.get(`option[value="${category[1]}"]`).invoke('text').then((categoryText) => {
          cy.get('button').contains('Reset filters').click()
          .then(() => {
            cy.contains('Select one or more categories').click().then(() => {
              cy.get('#category-dropdown').contains(categoryText).click();
              cy.get('body').click(0, 0);
              cy.get('button').contains('Show results').click()
                .then(() => {
                  cy.get('table.ecl-table.ecl-table--zebra tbody tr').should('contain', categoryText);
                });
            });
          });
        });
      });
    })
  });

  it.skip('should filter by random number of filters', () => {
    const filters = ['platform_id', 'decision_ground', 'source_type', 'category', 'decision_visibility', 'decision_monetary', 'decision_provision', 'decision_account', 'account_type', 'category_specification'];
    const labels = ['Select one or more platforms', 'Select one or more decision grounds', 'Select one or more information sources', 'Select one or more categories', 'Select one or more visibility restrictions', 'Select one or more monetary restrictions', 'Select one or more service provision', 'Select one or more account restrictions', 'Select one or more account types', 'Select one or more keywords'];

    const randomFilters = _.sampleSize(filters, 2, _.random(3, 6));

    const pairs = randomFilters.map(f => ({
      filter: f,
      label: labels[filters.indexOf(f)]
    }));

    cy.wrap(pairs).each(({ filter, label }) => {
      const maxRandom = Cypress._.random(1, 5);
      cy.selectRandomOptions(label, `#${filter}`, 1, maxRandom);
    });

    cy.get('button').contains('Show results').click()
      .then(() => {
       cy.get('p').contains('Showing 1 to').invoke('text')
          .then((text) => {
            const match = text.split('of')[1].match(/(\d+)/)[0];
            const randomFilterCount = parseInt(match[1]);
            expect(randomFilterCount).to.be.lt(initialResultsCount);
          });
      });
  });

  // it('should filter by date', () => {

  // });
});

