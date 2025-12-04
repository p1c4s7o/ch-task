package main

import (
	"bytes"
	"github.com/valyala/fasthttp"
	"github.com/wI2L/jettison"
	"os"
	"os/exec"
	"strconv"
	"strings"
)

var (
	//Service = "systemctl"
	Service = "supervisorctl"
)

type Result struct {
	Success bool   `json:"success"`
	Output  string `json:"output"`
	Error   string `json:"error"`
}

func result(success bool, output, errorMsg string) Result {
	return Result{
		Success: success,
		Output:  output,
		Error:   errorMsg,
	}
}

func runCommand(cmd []string) Result {
	var stdout, stderr bytes.Buffer

	c := exec.Command(cmd[0], cmd[1:]...)
	c.Stdout = &stdout
	c.Stderr = &stderr

	err := c.Run()

	if err != nil {
		return result(false, stdout.String(), stderr.String())
	}

	return result(true, stdout.String(), "")
}

func reload() Result {
	return runCommand([]string{"nginx", "-s", "reload"})
}

func restart() Result {
	return runCommand([]string{Service, "restart", "nginx"})
}

func start() Result {
	return runCommand([]string{Service, "start", "nginx"})
}

func stop() Result {
	return runCommand([]string{Service, "stop", "nginx"})
}

func test() Result {
	return runCommand([]string{"nginx", "-t"})
}

func status() Result {
	return runCommand([]string{Service, "status", "nginx"})
}

var commands = map[string]func() Result{
	"reload":  reload,
	"restart": restart,
	"start":   start,
	"stop":    stop,
	"test":    test,
	"status":  status,
}

func handler(ctx *fasthttp.RequestCtx) {
	if !ctx.IsPost() {
		writeJSON(ctx, result(false, "", "Method Not Allowed"))
		ctx.SetStatusCode(405)
		return
	}

	path := strings.Trim(string(ctx.Path()), "/")
	parts := strings.Split(path, "/")

	if len(parts) < 2 {
		writeJSON(ctx, result(false, "", "Token not found"))
		ctx.SetStatusCode(401)
		return
	}

	token, fn := parts[0], parts[1]

	secToken := os.Getenv("SEC_TOKEN")
	if secToken == "" || token != secToken {
		writeJSON(ctx, result(false, "", "Forbidden"))
		ctx.SetStatusCode(403)
		return
	}

	commandFn, ok := commands[fn]
	if !ok {
		writeJSON(ctx, result(false, "", "Command not found"))
		ctx.SetStatusCode(404)
		return
	}

	res := commandFn()

	code := 200
	if !res.Success {
		code = 500
	}

	ctx.SetStatusCode(code)
	writeJSON(ctx, res)
}

func writeJSON(ctx *fasthttp.RequestCtx, v interface{}) {
	ctx.Response.Header.SetContentType("application/json")
	data, _ := jettison.Marshal(v)
	ctx.Write(data)
}

func main() {
	listenPort := "9000"
	port := os.Getenv("BACKEND_PORT")
	if port != "" {
		intVar, _ := strconv.Atoi(port)
		if !(intVar < 1 || intVar > 65535) {
			listenPort = strconv.Itoa(intVar)
		}
	}
	fasthttp.ListenAndServe(":"+listenPort, handler)
}
