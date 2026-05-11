# LiquidRazor Transport Contracts

Transport-neutral contracts for concrete LiquidRazor transport kernels.

This package defines the shared vocabulary used by LiquidRazor transport kernels to normalize native transport input, process transport-level pipelines, and emit transport-native output.

It does **not** define request/response semantics.

It does **not** depend on the LiquidRazor MicroKernel.

It does **not** provide transport implementations.

## Table of Contents

- [Purpose](#purpose)
- [Architectural Position](#architectural-position)
- [Core Concepts](#core-concepts)
- [Transport Input](#transport-input)
- [Transport Output](#transport-output)
- [Transport Context](#transport-context)
- [Transport Metadata](#transport-metadata)
- [Pipelines and Filters](#pipelines-and-filters)
- [Adapters and Emitters](#adapters-and-emitters)
- [Exception Mapping](#exception-mapping)
- [Concrete Transport Kernels](#concrete-transport-kernels)
- [Package Boundaries](#package-boundaries)

## Purpose

`liquidrazor/transport-contracts` defines transport-neutral contracts for the boundary between native transport runtimes and concrete LiquidRazor transport kernels.

A concrete transport kernel receives native input from a specific runtime, adapts it into a transport input, processes it through transport-specific pipeline logic, and emits transport-native output.

Native transport input may come from HTTP servers, CLI invocations, gRPC calls, queue messages, WebSocket events, or future transport runtimes.

This package does not try to make those transports identical. It defines the common contract language needed for concrete kernels to compose transport behavior consistently without forcing transport-specific concepts into shared abstractions.

## Architectural Position

Transport Contracts sits between concrete transport kernels and transport-specific runtime code.

A final transport kernel is composed from:

```text
transport-specific runtime
+ transport-specific contracts
+ transport-contracts
+ micro-kernel
```

Example:

```text
HttpKernel
  = HTTP runtime
  + HTTP request/response contracts
  + transport-contracts
  + micro-kernel
```

The MicroKernel owns isolated work execution.

Concrete transport kernels own transport semantics.

Transport Contracts defines only the neutral boundary language shared by those kernels.

## Core Concepts

The core concepts are:

- `TransportInputInterface`
- `TransportOutputInterface`
- `TransportContextInterface`
- `TransportMetadataInterface`
- `TransportPipelineInterface`
- `TransportFilterInterface`
- `TransportAdapterInterface`
- `TransportEmitterInterface`
- `TransportExceptionMapperInterface`
- `TransportStatus`

Together, these describe the generic transport flow:

```text
native transport input
  -> adapted transport input
  -> transport context
  -> transport pipeline
  -> transport output
  -> emitted native transport output
```

They do not describe HTTP, CLI, gRPC, queue, or WebSocket behavior directly.

## Transport Input

A transport input represents input normalized enough for a transport pipeline while preserving transport ownership of the payload.

It identifies the transport, the operation being handled when known, the payload, and associated metadata.

Example interpretations:

```text
HTTP:
  transport name: http
  operation name: route name, controller id, or method/path
  payload: HTTP request object

CLI:
  transport name: cli
  operation name: command name
  payload: command input object

gRPC:
  transport name: grpc
  operation name: Service.Method
  payload: generated protobuf request

Queue:
  transport name: amqp
  operation name: routing key or consumer name
  payload: message envelope
```

The payload remains transport-owned. This package does not impose a shared body model, stream model, request model, or message schema.

## Transport Output

A transport output represents the result of transport-level processing before native emission.

It identifies the transport, a generic transport status, the payload, and associated metadata.

`TransportStatus` is transport-neutral. Concrete transport kernels decide how those statuses map to native behavior.

Examples:

```text
HTTP:
  success    -> successful HTTP response
  rejected   -> client error response
  timed_out  -> timeout response
  failed     -> server error response

CLI:
  success    -> success exit code
  rejected   -> validation or usage exit code
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

Transport-specific status codes, acknowledgment decisions, and protocol details belong to concrete transport kernels.

## Transport Context

A transport context carries neutral execution metadata relevant to transport pipeline handling.

It may include transport name, operation name, correlation id, causation id, trace id, start time, deadline, and transport metadata.

It does not represent MicroKernel runtime state. Worker process ids, child process state, fork state, runtime scopes, signal state, and micro-kernel lifecycle state belong to MicroKernel-related packages.

## Transport Metadata

Transport metadata is a generic key/value metadata boundary.

It allows transport kernels to pass structured information without forcing every transport to share the same native representation.

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

If metadata becomes strongly transport-specific, that concept belongs in a transport-specific package.

## Pipelines and Filters

A transport pipeline processes transport input and produces transport output.

A transport filter is one stage in that pipeline.

This package defines the neutral shape of those concepts. Concrete transport kernels provide the actual filters and pipeline behavior.

Examples of concrete filters include CORS handling for HTTP, argument validation for CLI, metadata validation for gRPC, and idempotency handling for queue runtimes.

## Adapters and Emitters

A transport adapter converts native transport input into `TransportInputInterface`.

A transport emitter converts `TransportOutputInterface` into transport-native output behavior.

Examples:

```text
HTTP native request -> transport input -> HTTP response
CLI argv/stdin      -> transport input -> stdout/stderr/exit code
gRPC call           -> transport input -> gRPC response/status
Queue message       -> transport input -> ack/nack/retry/dead-letter
```

Adapters and emitters are transport-owned. This package defines their shared contract shape only.

## Exception Mapping

Different transports represent failure differently.

A transport exception mapper converts an exception into a transport output according to the concrete transport semantics.

Examples:

```text
Validation exception:
  HTTP  -> client error response
  CLI   -> validation exit code
  gRPC  -> INVALID_ARGUMENT
  Queue -> nack or dead-letter behavior

Timeout exception:
  HTTP  -> timeout response
  CLI   -> timeout exit code
  gRPC  -> DEADLINE_EXCEEDED
  Queue -> retry or dead-letter behavior
```

The mapping contract is shared. The policy belongs to the concrete transport kernel.

## Concrete Transport Kernels

Concrete transport kernels are responsible for their own transport semantics.

An HTTP kernel owns HTTP request and response behavior.

A CLI kernel owns command input, command output, standard streams, and exit code behavior.

A gRPC kernel owns generated message handling, call metadata, status mapping, and streaming behavior when supported.

A queue kernel owns message envelopes, acknowledgment behavior, retry behavior, dead-letter behavior, and visibility timeout semantics.

Those responsibilities are intentionally not pushed down into `transport-contracts`.

## Package Boundaries

This package does not provide:

- a universal `RequestInterface`
- a universal `ResponseInterface`
- HTTP abstractions
- CLI command abstractions
- gRPC abstractions
- queue message abstractions
- routing
- controller dispatching
- middleware discovery
- dependency injection
- event dispatching
- process supervision
- worker lifecycle handling
- request serialization
- response serialization
- protocol clients
- protocol servers

Request/response semantics belong only to transports that actually have request/response semantics.

HTTP owns HTTP request/response.

CLI owns command input/output.

gRPC owns generated message calls and statuses.
Queues own message acknowledgment behavior.
