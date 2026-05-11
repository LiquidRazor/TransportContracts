# LiquidRazor Transport Contracts

Transport-neutral contracts for concrete LiquidRazor transport kernels.

This package defines the minimal shared vocabulary used by transport kernels such as HTTP, CLI, gRPC, queue, WebSocket, or future runtimes to normalize native transport input, run transport pipelines, and emit transport-native output.

It does **not** define request/response semantics.

It does **not** depend on the LiquidRazor MicroKernel.

It does **not** provide transport implementations.

Its job is to stay small, boring, and brutally clear.

## Table of Contents

* [Purpose](#purpose)
* [Architectural Position](#architectural-position)
* [Dependency Rules](#dependency-rules)
* [Core Concepts](#core-concepts)
* [Transport Input](#transport-input)
* [Transport Output](#transport-output)
* [Transport Context](#transport-context)
* [Transport Metadata](#transport-metadata)
* [Pipelines and Filters](#pipelines-and-filters)
* [Adapters and Emitters](#adapters-and-emitters)
* [Exception Mapping](#exception-mapping)
* [Concrete Transport Kernel Responsibilities](#concrete-transport-kernel-responsibilities)
* [Non-Goals](#non-goals)
* [Package Boundaries](#package-boundaries)
* [Expected Initial Structure](#expected-initial-structure)
* [Versioning Expectations](#versioning-expectations)

## Purpose

`liquidrazor/transport-contracts` defines contracts for transport-neutral execution boundaries.

A concrete transport kernel receives native input from a specific runtime, adapts it into a transport input, processes it through transport-specific filters, and emits transport-native output.

Examples of native transport input include:

* an HTTP server request
* CLI arguments and standard input
* a gRPC generated request and call context
* a queue message envelope
* a WebSocket frame or connection event

This package does not try to make those transports identical.

Instead, it defines the smallest common contract layer needed for final kernels to compose transport runtimes cleanly.

## Architectural Position

The intended architecture is:

```text
Concrete Transport Kernel
        |
        | uses
        v
Transport Contracts
        |
        | composed alongside
        v
MicroKernel
```

A final kernel is composed from:

```text
transport-specific runtime
+ transport-specific contracts
+ transport-contracts
+ micro-kernel
```

For example:

```text
HttpKernel
  = HTTP runtime
  + HTTP request/response contracts
  + transport-contracts
  + micro-kernel
```

The MicroKernel owns isolated work execution.

Concrete transport kernels own transport semantics.

Transport Contracts define only the neutral boundary between those worlds.

## Dependency Rules

This package must remain independent.

Required rules:

```text
transport-contracts must not depend on micro-kernel.
micro-kernel must not depend on transport-contracts.
final transport kernels compose both.
```

Allowed dependency direction:

```text
http-kernel
  -> http-contracts
  -> transport-contracts
  -> micro-kernel
```

The above means `http-kernel` may depend on both `transport-contracts` and `micro-kernel`, but neither base package depends on the other.

This package must not depend on:

* `liquidrazor/micro-kernel`
* `liquidrazor/event-manager`
* `liquidrazor/diregistry`
* `liquidrazor/file-locator`
* `liquidrazor/class-locator`
* PSR-7
* PSR-15
* PSR-11
* Symfony components
* gRPC libraries
* queue clients
* any framework runtime

This package is a contract boundary. It is not a service container, event system, runtime, router, dispatcher, or adapter implementation package.

## Core Concepts

The initial core concepts are:

* `TransportInputInterface`
* `TransportOutputInterface`
* `TransportContextInterface`
* `TransportMetadataInterface`
* `TransportPipelineInterface`
* `TransportFilterInterface`
* `TransportAdapterInterface`
* `TransportEmitterInterface`
* `TransportExceptionMapperInterface`
* `TransportStatus`

These contracts describe:

```text
native transport input
  -> adapted transport input
  -> transport context
  -> transport pipeline
  -> transport output
  -> emitted native transport output
```

They do not describe HTTP, CLI, gRPC, or queue behavior directly.

## Transport Input

A transport input represents input normalized enough for a transport pipeline, while still preserving transport ownership of the payload.

A transport input should expose:

* transport name
* operation name, when known
* payload
* metadata

Example interpretation:

```text
HTTP:
  transport name: http
  operation name: route name, controller id, or method/path
  payload: HttpRequestInterface

CLI:
  transport name: cli
  operation name: command name
  payload: CommandInputInterface

gRPC:
  transport name: grpc
  operation name: Service.Method
  payload: generated protobuf request

Queue:
  transport name: amqp
  operation name: routing key or consumer name
  payload: message envelope
```

The payload type must remain transport-owned.

This package must not impose a shared body model, stream model, request model, or message schema.

## Transport Output

A transport output represents the result of transport-level processing before native emission.

A transport output should expose:

* transport name
* generic transport status
* payload
* metadata

The status is generic and transport-neutral.

Suggested initial statuses:

```text
success
failed
rejected
cancelled
timed_out
deferred
no_output
```

Concrete transport kernels decide how those statuses map to native behavior.

Examples:

```text
HTTP:
  success    -> 200, 201, or 204
  rejected   -> 400, 403, or 422
  timed_out  -> 504
  failed     -> 500

CLI:
  success    -> exit code 0
  rejected   -> validation/error exit code
  timed_out  -> timeout exit code
  failed     -> generic failure exit code

gRPC:
  success    -> OK
  rejected   -> INVALID_ARGUMENT, PERMISSION_DENIED, or FAILED_PRECONDITION
  timed_out  -> DEADLINE_EXCEEDED
  failed     -> INTERNAL

Queue:
  success    -> ack
  rejected   -> nack or dead-letter
  timed_out  -> retry or dead-letter
  failed     -> retry, nack, or dead-letter
```

Transport-specific status codes, acknowledgment decisions, and protocol details do not belong in this package.

## Transport Context

A transport context carries neutral execution metadata relevant to the transport pipeline.

It may include:

* transport name
* operation name
* correlation id
* causation id
* trace id
* start time
* deadline
* transport metadata

It must not include micro-kernel runtime state.

The following do not belong in transport context:

* worker process id
* child process state
* fork state
* runtime scope instance
* signal state
* micro-kernel lifecycle state

Those belong to the MicroKernel and related runtime packages.

## Transport Metadata

Transport metadata is a generic key/value metadata boundary.

It exists so transport kernels can pass structured context without forcing every transport to share the same native representation.

Examples:

```text
HTTP:
  selected route
  normalized host
  content type
  accepted formats

CLI:
  verbosity
  interactive mode
  working directory

gRPC:
  method metadata
  deadline metadata
  peer metadata

Queue:
  routing key
  delivery id
  retry count
  producer id
```

Metadata must not become a dumping ground for transport-specific runtime objects.

If a transport needs strong semantics, that transport should define its own contracts in its own package.

## Pipelines and Filters

A transport pipeline processes transport input and produces transport output.

A transport filter is a pipeline component.

This package defines only the generic shape of those concepts.

Actual filters belong to concrete transport kernel packages.

Examples:

```text
HTTP filters:
  CORS
  trusted proxy handling
  body parsing
  content negotiation
  route matching
  cookie/session extraction

CLI filters:
  argument validation
  option normalization
  verbosity handling
  stdin parsing

gRPC filters:
  metadata validation
  deadline enforcement
  protobuf validation
  interceptor handling

Queue filters:
  idempotency
  deduplication
  retry policy
  dead-letter routing
```

This package must not implement transport-specific filters.

## Adapters and Emitters

A transport adapter converts native transport input into `TransportInputInterface`.

Examples:

```text
HTTP native request -> TransportInputInterface
CLI argv/stdin      -> TransportInputInterface
gRPC call           -> TransportInputInterface
Queue message       -> TransportInputInterface
```

A transport emitter converts `TransportOutputInterface` into transport-native output behavior.

Examples:

```text
TransportOutputInterface -> HTTP response
TransportOutputInterface -> CLI stdout/stderr/exit code
TransportOutputInterface -> gRPC response/status
TransportOutputInterface -> queue ack/nack/retry/dead-letter
```

Adapters and emitters are defined here only as contracts.

Concrete implementations belong in concrete transport kernel packages.

## Exception Mapping

Different transports represent failure differently.

A transport exception mapper converts a thrown exception into a `TransportOutputInterface`.

The same exception may map differently depending on the transport.

Examples:

```text
Validation exception:
  HTTP  -> 422 response
  CLI   -> validation exit code
  gRPC  -> INVALID_ARGUMENT
  Queue -> nack without retry or dead-letter

Timeout exception:
  HTTP  -> 504 response
  CLI   -> timeout exit code
  gRPC  -> DEADLINE_EXCEEDED
  Queue -> retry or dead-letter
```

This package defines the exception mapping contract only.

Concrete exception policy belongs to the final transport kernel.

## Concrete Transport Kernel Responsibilities

A concrete transport kernel is responsible for:

* loading only its own transport contracts
* loading only its own transport filters
* adapting native input into transport input
* creating transport context
* running the transport pipeline
* composing with the MicroKernel where isolated work execution is needed
* mapping micro-kernel work results back into transport output
* emitting native transport output
* mapping exceptions according to transport semantics

For example, an HTTP kernel owns:

* HTTP request contracts
* HTTP response contracts
* HTTP filters
* HTTP route/controller integration
* HTTP response emission
* HTTP exception-to-response mapping

A CLI kernel owns:

* command input contracts
* command output contracts
* command discovery
* stdout/stderr behavior
* exit code mapping

A gRPC kernel owns:

* generated request/response handling
* gRPC call metadata
* gRPC status mapping
* streaming semantics, when supported

A queue kernel owns:

* message envelopes
* acknowledgment behavior
* retry behavior
* dead-letter behavior
* visibility timeout semantics

These must not be pushed down into `transport-contracts`.

## Non-Goals

This package does not provide:

* a universal `RequestInterface`
* a universal `ResponseInterface`
* HTTP abstractions
* CLI command abstractions
* gRPC abstractions
* queue message abstractions
* routing
* controller dispatching
* middleware discovery
* dependency injection
* event dispatching
* process supervision
* worker lifecycle handling
* request serialization
* response serialization
* protocol clients
* protocol servers

Most importantly:

```text
This package does not provide a universal RequestInterface or ResponseInterface.
```

Request/response semantics belong to transports that actually have request/response semantics.

HTTP owns HTTP request/response.

CLI owns command input/output.

gRPC owns generated message calls and statuses.

Queues own message acknowledgment behavior.

## Package Boundaries

This package should remain mostly interfaces, enums, exceptions, and tiny immutable values.

It may provide minimal reusable value objects only when they are genuinely transport-neutral.

Allowed examples:

* array-backed metadata
* generic transport result
* generic transport identity

Disallowed examples:

* HTTP headers
* URI objects
* route objects
* command definitions
* queue delivery objects
* gRPC metadata wrappers
* worker state
* runtime scopes
* filter registries
* adapter registries
* container integration
* event listeners

If a concept requires transport-specific vocabulary, it belongs in a transport-specific package.

## Expected Initial Structure

Expected initial structure:

```text
include/
  Contract/
    TransportInputInterface.php
    TransportOutputInterface.php
    TransportContextInterface.php
    TransportMetadataInterface.php
    TransportPipelineInterface.php
    TransportFilterInterface.php
    TransportAdapterInterface.php
    TransportEmitterInterface.php
    TransportExceptionMapperInterface.php

  Enum/
    TransportStatus.php

  Exception/
    TransportExceptionInterface.php
    UnsupportedTransportInputException.php
    TransportPipelineException.php
    TransportEmissionException.php

lib/
  Metadata/
    ArrayTransportMetadata.php
```

Additional contracts should be added only when required by a concrete transport kernel.

The first expected consumer is `liquidrazor/http-kernel`.

## Versioning Expectations

The initial version should remain conservative.

Version `v0.1.0` should establish only the minimal contract vocabulary needed for the first transport kernel.

The package should evolve based on real pressure from concrete kernels, not imagined future transports.

Expansion should be driven by concrete implementation needs from packages such as:

* `liquidrazor/http-kernel`
* `liquidrazor/cli-kernel`
* `liquidrazor/grpc-kernel`
* `liquidrazor/queue-kernel`

Contracts added too early become permanent mistakes.

This package should prefer fewer contracts with clearer boundaries over broad abstractions that pretend all transports are the same thing.
