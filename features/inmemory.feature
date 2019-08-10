@inmemory
Feature: Circuit breaker can interact within the PHP application
  In order to use the circuit breaker on a single PHP application
  As an engineer
  I need to verify that the library behave as expected

  Scenario: The circuit breaker fails after all the tries when using the in memory persistence adapter
    Given A circuit breaker instance using in memory persistence adapter with "3" retries and a reset timeout of "5" seconds
    And it is wrapping a function that throws an exception
    And the circuit breaker will be in a "closed" state with "0" failures
    When the function is executed
    Then the circuit breaker will throw the exception from the function
    And the circuit breaker will be in a "open" state with "3" failures
    And the circuit breaker will have last failure set
    When waiting for "1" seconds
    Then the circuit breaker will throw an circuit breaker is open exception executing the function
    When waiting for "5" seconds
    Then the circuit breaker will be in a "half_open" state with "3" failures
    When it is wrapping a function that throws an exception for "0" times
    And the function is executed
    Then the circuit breaker will be in a "closed" state with "0" failures
    And the circuit breaker will have last failure not set

  Scenario: The circuit breaker fails after all the tries when using the in memory persistence adapter
    Given A circuit breaker instance using in memory persistence adapter with "3" retries and a reset timeout of "15" seconds
    And it is wrapping a function that throws an exception
    And the circuit breaker will be in a "closed" state with "0" failures
    When the function is executed
    Then the circuit breaker will throw the exception from the function
    And the circuit breaker will be in a "open" state with "3" failures
    And the circuit breaker will have last failure set
    When waiting for "1" seconds
    Then the circuit breaker will throw an circuit breaker is open exception executing the function
    When waiting for "5" seconds
    Then the circuit breaker will be in a "open" state with "3" failures

  Scenario: The circuit breaker succeed after some failure when using the in memory persistence adapter
    Given A circuit breaker instance using in memory persistence adapter with "3" retries and a reset timeout of "5" seconds
    And it is wrapping a function that throws an exception for "2" times
    And the circuit breaker will be in a "closed" state with "0" failures
    When the function is executed
    Then the circuit breaker will be in a "closed" state with "0" failures
    And the circuit breaker will have last failure not set

  Scenario: The circuit breaker succeed at the first attempt when using the in memory persistence adapter
    Given A circuit breaker instance using in memory persistence adapter with "3" retries and a reset timeout of "5" seconds
    And it is wrapping a function that throws an exception for "0" times
    And the circuit breaker will be in a "closed" state with "0" failures
    When the function is executed
    Then the circuit breaker will be in a "closed" state with "0" failures
    And the circuit breaker will have last failure not set
