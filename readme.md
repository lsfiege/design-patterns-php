# Practice design patterns using Laravel examples

- Adapter
- Strategy
- Factory pattern combined with both

## Adapter Pattern

- Integrating 3rd party APIs
- Depedency Inversion
- Use the container to swap implementations

### Benefits

- Inverts dependency
- Ability to swap out implementations
- Easy to test

## Strategy Pattern

- Clean up branching logic in large cases
- In-depth refactoring

### Benefits

- Simplifies containing classes by removing conditional logic
- Allows to defer decisions until runtime
- Makes the clauses using the strategies "pluggable"

## Factory Pattern

- Identify factories hidding in code
- Extact class refactor
- Take advantaje of Auto-wiring Service Container

### Primmer

- Only responsible for creating objects of a specific type
- Encapsulate decision process for choosing the appropriate concretion

### Benefits

- Moves creation logic out of dependant classes
- Simple and composable
- Lean on the Service Container to construct and inject

> Based on [Colin Decarlo "Design Patterns with Laravel" Laracon 2018 talk](https://www.youtube.com/watch?v=e4ugSgGaCQ0)
