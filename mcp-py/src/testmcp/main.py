import logging
import subprocess
import sys
from argparse import ArgumentParser
from pathlib import Path

from mcp.server.fastmcp import FastMCP
from mcp.server.fastmcp.utilities.logging import get_logger


from testmcp.events.tool import EventsTool
from testmcp.kino.tool import KinoTool


# Server configuration
port = 8000
host = "0.0.0.0"


# Initialize FastMCP server
mcp = FastMCP("firsttest",
              host=host, 
              port=port)


def run_server() -> None:
    """ Run the MCP server without watch mode. """
    to_client_logger = get_logger(name="fastmcp.server.context.to_client")
    to_client_logger.setLevel(level=logging.INFO)
    
    uri_tools = [
        EventsTool,
        KinoTool
    ]
    
    for tool_cls in uri_tools:
        print(f"Registering tool: {tool_cls.__name__}")
        try:
            tool_cls.create(mcp)
        except Exception as e:
            print(f"Error registering tool {tool_cls.__name__}: {e}")
    

    # Initialize and run the server
    mcp.run(transport="streamable-http")


def _stop_process(process: subprocess.Popen) -> None:
    """ Stop a subprocess gracefully, terminating if necessary. """
    if process.poll() is not None:
        return

    process.terminate()
    try:
        process.wait(timeout=5)
    except subprocess.TimeoutExpired:
        process.kill()
        process.wait(timeout=5)


def run_with_watch() -> None:
    """ Run the MCP server with watch mode enabled, restarting on source changes. """
    try:
        from watchfiles import watch
    except ImportError as exc:
        raise RuntimeError(
            "Missing dependency 'watchfiles'. Install dependencies and retry."
        ) from exc

    src_root = Path(__file__).resolve().parents[1]
    cmd = [sys.executable, "-m", "testmcp.main", "--no-watch"]
    process = subprocess.Popen(cmd)

    try:
        for changes in watch(str(src_root), recursive=True):
            if not any(path.endswith(".py") for _, path in changes):
                continue

            print("Python change detected. Restarting MCP server...")
            _stop_process(process)
            process = subprocess.Popen(cmd)
    except KeyboardInterrupt:
        pass
    finally:
        _stop_process(process)


def main() -> None:
    """ Main entry point for running the MCP server. Parses command-line arguments and starts the server with optional watch mode. """
    parser = ArgumentParser(description="Run the MCP server")
    parser.add_argument("--watch", action="store_true", help="Restart on source changes")
    parser.add_argument("--no-watch", action="store_true", help="Disable watch mode")
    args = parser.parse_args()

    should_watch = args.watch and not args.no_watch
    if should_watch:
        run_with_watch()
    else:
        run_server()


if __name__ == "__main__":
    main()